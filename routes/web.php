<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProcurementOrderController;
use App\Http\Controllers\StaffPasswordResetController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/landing', function () {
    return view('landing');
});

/* ── Admin Dashboard (admin only) ────────────────────────────── */
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* ── User Management Module (Admin only) ─────────────────────── */
Route::middleware(['auth', 'admin'])->group(function () {

    // Staff CRUD (REQ008, REQ009, REQ012)
    Route::resource('users', UserController::class)
         ->except(['destroy']);

    Route::put('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
         ->name('users.toggle-status');

    // Staff password reset flow (REQ010, REQ011)
    Route::get('users/{user}/reset-password',  [StaffPasswordResetController::class, 'create'])
         ->name('users.password-reset.create');

    Route::post('users/{user}/reset-password', [StaffPasswordResetController::class, 'store'])
         ->name('users.password-reset.store');

    Route::get('password-reset-requests', [StaffPasswordResetController::class, 'index'])
         ->name('password-reset-requests.index');

    Route::put('password-reset-requests/{passwordResetRequest}/approve', [StaffPasswordResetController::class, 'approve'])
         ->name('password-reset-requests.approve');

    Route::put('password-reset-requests/{passwordResetRequest}/reject',  [StaffPasswordResetController::class, 'reject'])
         ->name('password-reset-requests.reject');

    // Menu Catalog Management (REQ016–REQ020)
    Route::resource('menu', MenuItemController::class)
         ->except(['destroy'])
         ->parameters(['menu' => 'menu']);

    Route::put('menu/{menu}/toggle-status', [MenuItemController::class, 'toggleStatus'])
         ->name('menu.toggle-status');

    // Customer Account Management (REQ013–REQ015)
    Route::resource('customers', CustomerController::class)
         ->only(['index', 'show']);

    Route::put('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])
         ->name('customers.toggle-status');

    // ── Stock Inventory Module (REQ021–REQ040) ────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',           [InventoryController::class, 'index'])      ->name('index');
        Route::get('/rtc',        [InventoryController::class, 'rtc'])        ->name('rtc');
        Route::get('/beverages',  [InventoryController::class, 'beverages'])  ->name('beverages');
        Route::get('/restocking', [InventoryController::class, 'restocking']) ->name('restocking');
        Route::get('/{item}/edit',[InventoryController::class, 'edit'])       ->name('edit');
        Route::put('/{item}',     [InventoryController::class, 'update'])     ->name('update');

        Route::get('/stock-in',   [StockInController::class, 'index'])  ->name('stock-in.index');
        Route::post('/stock-in',  [StockInController::class, 'store'])  ->name('stock-in.store');

        Route::get('/conversions', [ConversionController::class, 'index']) ->name('conversions.index');
        Route::post('/conversions',[ConversionController::class, 'store']) ->name('conversions.store');

        Route::get('/adjustments', [InventoryAdjustmentController::class, 'index']) ->name('adjustments.index');
        Route::post('/adjustments',[InventoryAdjustmentController::class, 'store']) ->name('adjustments.store');
    });

    // ── Purchase Order Module (REQ041–REQ046) ─────────────────────
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/',                                [ProcurementOrderController::class, 'index'])   ->name('index');
        Route::post('/generate',                       [ProcurementOrderController::class, 'generate']) ->name('generate');
        Route::get('/{purchaseOrder}',                 [ProcurementOrderController::class, 'show'])     ->name('show');
        Route::get('/{purchaseOrder}/edit',            [ProcurementOrderController::class, 'edit'])     ->name('edit');
        Route::put('/{purchaseOrder}',                 [ProcurementOrderController::class, 'update'])   ->name('update');
        Route::post('/{purchaseOrder}/finalize',       [ProcurementOrderController::class, 'finalize']) ->name('finalize');
        Route::get('/{purchaseOrder}/print',           [ProcurementOrderController::class, 'print'])    ->name('print');
        Route::delete('/{purchaseOrder}',              [ProcurementOrderController::class, 'destroy'])  ->name('destroy');
    });

    // ── Discount Management Module (REQ051–REQ056) ────────────────
    Route::resource('discounts', DiscountController::class)->except(['destroy']);
    Route::put('discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])
         ->name('discounts.toggle-status');
});

/* ── Kitchen Display System (Kitchen Staff only) — Module 17 ──── */
Route::middleware(['auth', 'kitchen_staff'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/',                 [KitchenController::class, 'index'])       ->name('index');
    Route::get('/orders',           [KitchenController::class, 'orders'])      ->name('orders');
    Route::patch('/orders/{order}/status', [KitchenController::class, 'updateStatus']) ->name('orders.status');
});

/* ── Customer Profile Module (REQ063–REQ065) ─────────────────── */
Route::middleware(['auth:customer', 'customer'])->prefix('account')->name('account.')->group(function () {
    Route::get('/',          [CustomerProfileController::class, 'index'])          ->name('index');
    Route::put('/profile',   [CustomerProfileController::class, 'updateProfile'])  ->name('profile.update');
    Route::put('/password',  [CustomerProfileController::class, 'updatePassword']) ->name('password.update');
    Route::get('/orders/{order}', [CustomerProfileController::class, 'showOrder']) ->name('orders.show');
    Route::get('/orders/{order}/status', [CustomerProfileController::class, 'orderStatus']) ->name('orders.status');
});

/* ── Digital Menu Catalog (Customer only) ────────────────────── */
Route::middleware(['auth:customer', 'customer'])->group(function () {

    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

    // Cart endpoints (JSON when AJAX, full page when browser navigation)
    Route::get('/cart',                      [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add',                 [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{cartItem}/update',  [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear',             [CartController::class, 'clear'])->name('cart.clear');

    // ── Order Module: Checkout (REQ071–REQ075) ────────────────────
    Route::get('/checkout',  [CheckoutController::class, 'index']) ->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store']) ->name('checkout.store');
});

require __DIR__.'/auth.php';
