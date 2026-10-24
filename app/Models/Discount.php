<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'discount_name', 'discount_type', 'discount_value',
        'eligibility_type', 'minimum_purchase', 'maximum_discount',
        'start_date', 'end_date', 'description', 'status',
    ];

    protected $casts = [
        'discount_value'   => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'start_date'       => 'date',
        'end_date'         => 'date',
    ];

    /* ── Constants ── */
    const TYPES = [
        'percentage' => 'Percentage Discount',
        'fixed'      => 'Fixed Amount Discount',
        'special'    => 'Special Discount (Requires Approval)',
    ];

    const ELIGIBILITY = [
        'senior_citizen'   => 'Senior Citizen',
        'pwd'              => 'Person With Disability (PWD)',
        'promotional'      => 'Promotional Discount',
        'employee'         => 'Employee Discount',
        'minimum_purchase' => 'Minimum Purchase',
        'date_range'       => 'Specific Date Range',
        'all_customers'    => 'All Customers',
    ];

    /* ── Scopes ── */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeInactive(Builder $q): Builder
    {
        return $q->where('status', 'inactive');
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->whereNotNull('end_date')->where('end_date', '<', today());
    }

    /* ── Computed Attributes ── */
    public function getFormattedValueAttribute(): string
    {
        if ($this->discount_type === 'special') {
            return 'Cashier-Requested Amount';
        }
        if ($this->discount_type === 'percentage') {
            return number_format($this->discount_value, 0) . '%';
        }
        return '₱' . number_format($this->discount_value, 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->discount_type] ?? ucfirst($this->discount_type);
    }

    public function getEligibilityLabelAttribute(): string
    {
        return self::ELIGIBILITY[$this->eligibility_type] ?? ucfirst($this->eligibility_type);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getValidityPeriodAttribute(): string
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('M d, Y') . ' – ' . $this->end_date->format('M d, Y');
        }
        if ($this->start_date) {
            return 'From ' . $this->start_date->format('M d, Y');
        }
        if ($this->end_date) {
            return 'Until ' . $this->end_date->format('M d, Y');
        }
        return 'No Expiry';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->is_expired && $this->status === 'active') return 'badge-disc-expired';
        return $this->status === 'active' ? 'badge-disc-active' : 'badge-disc-inactive';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_expired && $this->status === 'active') return 'Expired';
        return $this->status === 'active' ? 'Active' : 'Inactive';
    }

    /* ── Billing Helpers (Payment & Billing Module) ── */

    /**
     * Whether this discount rule can currently be applied at all — active,
     * not expired, and (if a start date is set) already in effect.
     */
    public function isCurrentlyValid(): bool
    {
        if ($this->status !== 'active' || $this->is_expired) {
            return false;
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Whether the given order subtotal meets this discount's minimum purchase
     * requirement (if any is configured).
     */
    public function meetsMinimumPurchase(float $subtotal): bool
    {
        return $this->minimum_purchase === null || $subtotal >= (float) $this->minimum_purchase;
    }

    /**
     * True for eligibility types that require the cashier to physically verify
     * an ID before the discount may be applied (REQ099).
     */
    public function requiresEligibilityVerification(): bool
    {
        return in_array($this->eligibility_type, ['senior_citizen', 'pwd'], true);
    }

    /**
     * Special discounts don't carry a preset value — the cashier types an
     * amount per transaction, and it only takes effect once an admin
     * approves the specific SpecialDiscountRequest for that order.
     */
    public function isSpecial(): bool
    {
        return $this->discount_type === 'special';
    }

    /**
     * Senior Citizen and PWD sales are VAT-exempt (RA 9994 / RA 10754): the 12% VAT is
     * removed from the VAT-inclusive price first, and the discount applies to what's left.
     */
    public function isVatExempt(): bool
    {
        return $this->requiresEligibilityVerification();
    }

    /**
     * How this discount reduces a VAT-inclusive subtotal: the VAT removed (VAT-exempt
     * discounts only) and the discount itself. E.g. ₱100 senior → VAT ₱10.71 off, then
     * 20% of ₱89.29 = ₱17.86 off, ₱71.43 to pay.
     *
     * @return array{vatExemption: float, discountAmount: float}
     */
    public function reductionsFor(float $subtotal): array
    {
        if (! $this->isVatExempt()) {
            return ['vatExemption' => 0.0, 'discountAmount' => $this->computeDiscountAmount($subtotal)];
        }

        // Worked out in whole centavos, so halves always round up the same way on every PHP
        // version and in the billing screen's JavaScript (e.g. ₱3.78 ÷ 1.12 = ₱3.375 → ₱3.38)
        $subtotalC = (int) round($subtotal * 100);
        $baseC     = Invoice::divideHalfUp($subtotalC * 100, (int) round(100 * (1 + Invoice::VAT_RATE)));

        $discountC = $this->discount_type === 'percentage'
            ? Invoice::divideHalfUp($baseC * (int) round((float) $this->discount_value * 100), 10000)
            : (int) round((float) $this->discount_value * 100);
        if ($this->maximum_discount !== null) {
            $discountC = min($discountC, (int) round((float) $this->maximum_discount * 100));
        }
        $discountC = min($discountC, $baseC);

        return [
            'vatExemption'   => ($subtotalC - $baseC) / 100,
            'discountAmount' => $discountC / 100,
        ];
    }

    /**
     * Compute the peso discount amount for a given subtotal, respecting the
     * discount type and the optional maximum-discount cap. Not meaningful
     * for 'special' discounts — their amount comes from an approved
     * SpecialDiscountRequest instead (see CashierController::resolveDiscount()).
     */
    public function computeDiscountAmount(float $subtotal): float
    {
        if ($this->isSpecial()) {
            return 0.0;
        }

        $amount = $this->discount_type === 'percentage'
            ? $subtotal * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        if ($this->maximum_discount !== null) {
            $amount = min($amount, (float) $this->maximum_discount);
        }

        return round(min($amount, $subtotal), 2);
    }
}
