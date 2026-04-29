<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SidebarController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ── TailAdmin UI demo / auth pages ────────────────────────────────────────────
Route::get('/', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
})->name('home');

Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->middleware('guest')->name('signin');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }
    return back()->withErrors(['email' => 'Las credenciales no son correctas.'])->onlyInput('email');
})->middleware('guest')->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('signin');
})->middleware('auth')->name('logout');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// Sidebar state persistence (TailAdmin)
Route::post('/sidebar/toggle', [SidebarController::class, 'toggle'])->name('sidebar.toggle');

// =============================================================================
// PSF — Sistema de Gestión de Inventario
// =============================================================================

// ── Dashboard ─────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => view('pages.dashboard.ecommerce', ['title' => 'Dashboard']))->name('dashboard');
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
        Route::post('/',      [PurchaseController::class, 'store'])->name('store');
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
        Route::post('/',      [TransferController::class, 'store'])->name('store');
        Route::get('/{id}',   [TransferController::class, 'show'])->name('show');
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

// ── Sales / POS ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:master,admin,cashier'])
    ->prefix('sales')
    ->name('sales.')
    ->group(function () {
        // search-product must precede /{id} to avoid route collision
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
