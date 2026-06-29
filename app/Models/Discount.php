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
}
