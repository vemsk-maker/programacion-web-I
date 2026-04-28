<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SidebarController;
use Illuminate\Support\Facades\Route;

// ── TailAdmin UI demo / auth pages ────────────────────────────────────────────
Route::get('/', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
})->name('dashboard');

Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// Sidebar state persistence (TailAdmin)
Route::post('/sidebar/toggle', [SidebarController::class, 'toggle'])->name('sidebar.toggle');

// =============================================================================
// PSF — Sistema de Gestión de Inventario
// =============================================================================

// ── Dashboard ─────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', fn () => null)->name('index');
});

// ── Products ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}',      fn () => null)->name('show');
        Route::get('/{id}/edit', fn () => null)->name('edit');
        Route::put('/{id}',      fn () => null)->name('update');
        Route::delete('/{id}',   fn () => null)->name('destroy');
    });

// ── Inventory: Purchases ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('inventory/purchases')
    ->name('inventory.purchases.')
    ->group(function () {
        Route::get('/',       fn () => null)->name('index');
        Route::get('/create', fn () => null)->name('create');
        Route::post('/',      fn () => null)->name('store');
        Route::get('/{id}',   fn () => null)->name('show');
    });

// ── Inventory: Transfers ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('inventory/transfers')
    ->name('inventory.transfers.')
    ->group(function () {
        Route::get('/',       fn () => null)->name('index');
        Route::get('/create', fn () => null)->name('create');
        Route::post('/',      fn () => null)->name('store');
        Route::get('/{id}',   fn () => null)->name('show');
    });

// ── Inventory: Adjustments ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin'])
    ->prefix('inventory/adjustments')
    ->name('inventory.adjustments.')
    ->group(function () {
        Route::get('/',       fn () => null)->name('index');
        Route::get('/create', fn () => null)->name('create');
        Route::post('/',      fn () => null)->name('store');
        Route::get('/{id}',   fn () => null)->name('show');
    });

// ── Sales ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,cashier'])
    ->prefix('sales')
    ->name('sales.')
    ->group(function () {
        Route::get('/',             fn () => null)->name('index');
        Route::get('/create',       fn () => null)->name('create');
        Route::post('/',            fn () => null)->name('store');
        Route::get('/{id}',         fn () => null)->name('show');
        Route::post('/{id}/cancel', fn () => null)->name('cancel');
    });

// ── Reports ───────────────────────────────────────────────────────────────────
Route::middleware(['auth'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/stock',     fn () => null)->name('stock');
        Route::get('/movements', fn () => null)->name('movements');
        Route::get('/purchases', fn () => null)->name('purchases');
        Route::get('/sales',     fn () => null)->name('sales');
    });

// ── Admin: Users ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}/edit', fn () => null)->name('edit');
        Route::put('/{id}',      fn () => null)->name('update');
        Route::delete('/{id}',   fn () => null)->name('destroy');
    });

// ── Admin: Locations ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master'])
    ->prefix('admin/locations')
    ->name('admin.locations.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}/edit', fn () => null)->name('edit');
        Route::put('/{id}',      fn () => null)->name('update');
        Route::delete('/{id}',   fn () => null)->name('destroy');
    });


// ── Dashboard ─────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', fn () => null)->name('index');
});

// ── Products ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}',      fn () => null)->name('show');
        Route::get('/{id}/edit', fn () => null)->name('edit');
        Route::put('/{id}',      fn () => null)->name('update');
        Route::delete('/{id}',   fn () => null)->name('destroy');
    });

// ── Inventory: Purchases ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('inventory/purchases')
    ->name('inventory.purchases.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}',      fn () => null)->name('show');
    });

// ── Inventory: Transfers ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,warehouse_manager'])
    ->prefix('inventory/transfers')
    ->name('inventory.transfers.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}',      fn () => null)->name('show');
    });

// ── Inventory: Adjustments ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin'])
    ->prefix('inventory/adjustments')
    ->name('inventory.adjustments.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}',      fn () => null)->name('show');
    });

// ── Sales ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,cashier'])
    ->prefix('sales')
    ->name('sales.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}',      fn () => null)->name('show');
        Route::post('/{id}/cancel', fn () => null)->name('cancel');
    });

// ── Reports ───────────────────────────────────────────────────────────────────
Route::middleware(['auth'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/',           fn () => null)->name('index');
        Route::get('/stock',      fn () => null)->name('stock');
        Route::get('/movements',  fn () => null)->name('movements');
        Route::get('/purchases',  fn () => null)->name('purchases');
        Route::get('/sales',      fn () => null)->name('sales');
    });

// ── Admin: Users ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}/edit', fn () => null)->name('edit');
        Route::put('/{id}',      fn () => null)->name('update');
        Route::delete('/{id}',   fn () => null)->name('destroy');
    });

// ── Admin: Locations ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master'])
    ->prefix('admin/locations')
    ->name('admin.locations.')
    ->group(function () {
        Route::get('/',          fn () => null)->name('index');
        Route::get('/create',    fn () => null)->name('create');
        Route::post('/',         fn () => null)->name('store');
        Route::get('/{id}/edit', fn () => null)->name('edit');
        Route::put('/{id}',      fn () => null)->name('update');
        Route::delete('/{id}',   fn () => null)->name('destroy');
    });

