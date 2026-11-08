<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Protected Route
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'Logout'])
        ->name('logout');

    // Dashboard berdasarkan role
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/catalog', [CatalogController::class, 'index'])->middleware('role:user')->name('catalog.index');
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/fines', [FineController::class, 'index'])->name('fines.index');

    Route::middleware('role:admin')->group(function () {
        // Rute buku (Modul 04)
        Route::get('/books', [BookController::class, 'index'])->name('admin.book.index');
        Route::get('/books/create', [BookController::class, 'create'])->name('admin.book.create');
        Route::post('/books', [BookController::class, 'store'])->name('admin.book.store');
        Route::get('/books/{id}/edit', [BookController::class, 'edit'])->name('admin.book.edit');
        Route::put('/books/{id}', [BookController::class, 'update'])->name('admin.book.update');
        Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('admin.book.destroy');

        // Rute manajemen pengguna (Modul 05)
        Route::get('/users', [UserController::class, 'index'])->name('admin.user.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.user.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.user.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.user.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.user.destroy');

        Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
        Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
        Route::post('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');
        Route::post('/fines/{fine}/pay', [FineController::class, 'markPaid'])->name('fines.pay');
    });

});

// Guest Route
Route::middleware(['guest'])->group(function () {

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'Login']);
});
