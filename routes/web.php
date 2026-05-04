<?php

use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SidebarController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Autenticación y Home ──────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [HomeController::class, 'showLogin'])->middleware('guest')->name('login');
Route::get('/signin', fn() => redirect()->route('login'));
Route::post('/login', [HomeController::class, 'login'])->middleware('guest');
Route::post('/logout', [HomeController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/signup', [HomeController::class, 'showSignup'])->name('signup');

// Sidebar state persistence (TailAdmin)
Route::post('/sidebar/toggle', [SidebarController::class, 'toggle'])->name('sidebar.toggle');

// =============================================================================
// PSF — Sistema de Gestión de Inventario
// =============================================================================

// ── Dashboard ─────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
});

// ── Catálogo: Categorías ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])->group(function () {
    Route::resource('categories', CategoryController::class);
});

// ── Catálogo: Proveedores ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])->group(function () {
    Route::resource('suppliers', SupplierController::class);
    Route::patch('suppliers/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('suppliers.toggle');
});

// ── Catálogo: Productos ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])->group(function () {
    Route::resource('products', ProductController::class);
});

// ── Inventory: Purchases ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('inventory/purchases')
    ->name('inventory.purchases.')
    ->group(function () {
        Route::get('/',       [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        // Se cambió el POST a '/' para coincidir con la lógica del controlador
        Route::post('/',       [PurchaseController::class, 'store'])->name('store');
        Route::get('/{id}',   [PurchaseController::class, 'show'])->name('show');
    });

// ── Inventory: Transfers ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('inventory/transfers')
    ->name('inventory.transfers.')
    ->group(function () {
        Route::get('/stock',  [TransferController::class, 'getAvailableStock'])->name('stock');
        Route::get('/',       [TransferController::class, 'index'])->name('index');
        Route::get('/create', [TransferController::class, 'create'])->name('create');
        Route::post('/',       [TransferController::class, 'store'])->name('store');
        Route::get('/{id}',   [TransferController::class, 'show'])->name('show');
    });

// ── Inventory: Adjustments ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('inventory/adjustments')
    ->name('inventory.adjustments.')
    ->group(function () {
        Route::get('/',       [AdjustmentController::class, 'index'])->name('index');
        Route::get('/create', [AdjustmentController::class, 'create'])->name('create');
        Route::post('/',       [AdjustmentController::class, 'store'])->name('store');
        Route::get('/{id}',   [AdjustmentController::class, 'show'])->name('show');
    });

// ── Sales / POS ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,cashier'])
    ->prefix('sales')
    ->name('sales.')
    ->group(function () {
        Route::get('/search-product',   [SaleController::class, 'searchProduct'])->name('search');
        Route::get('/',                 [SaleController::class, 'index'])->name('index');
        Route::get('/create',           [SaleController::class, 'create'])->name('create');
        Route::post('/',                [SaleController::class, 'store'])->name('store');
        Route::get('/{id}',             [SaleController::class, 'show'])->name('show');
        Route::post('/{id}/cancel',     [SaleController::class, 'cancel'])->name('cancel');
    });

// ── Reports ───────────────────────────────────────────────────────────────────
Route::middleware(['auth'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/',             [ReportController::class, 'dashboard'])->name('index');
        Route::get('/expirations',  [ReportController::class, 'expirations'])->name('expirations');
        Route::get('/stock',        [ReportController::class, 'stock'])->name('stock');
        Route::get('/movements',    [ReportController::class, 'movements'])->name('movements');
    });

// ── Admin: Users ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('/',              [UserController::class, 'index'])->name('index');
        Route::get('/create',        [UserController::class, 'create'])->name('create');
        Route::post('/',             [UserController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [UserController::class, 'update'])->name('update');
        Route::delete('/{id}',       [UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [UserController::class, 'toggle'])->name('toggle');
    });

// ── Admin: Locations ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin'])
    ->prefix('admin/locations')
    ->name('admin.locations.')
    ->group(function () {
        Route::get('/',              [LocationController::class, 'index'])->name('index');
        Route::get('/create',        [LocationController::class, 'create'])->name('create');
        Route::post('/',             [LocationController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [LocationController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [LocationController::class, 'update'])->name('update');
        Route::delete('/{id}',       [LocationController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [LocationController::class, 'toggle'])->name('toggle');
    });