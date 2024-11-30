<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DataAccessController;
use App\Http\Controllers\DigitalSignatureController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    // Authentication routes
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // File management routes
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [FileController::class, 'index'])
            ->name('index');
        Route::get('/create', [FileController::class, 'create'])
            ->name('create');
        Route::post('/', [FileController::class, 'store'])
            ->name('store');
        Route::get('/{file}/download', [FileController::class, 'download'])
            ->name('download');
    });

    // Analysis route
    Route::get('/analysis', [FileController::class, 'analysis'])
        ->name('files.analysis');

    // Logout route
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

        Route::middleware(['auth'])->group(function () {
            Route::get('/data-access', [DataAccessController::class, 'index'])->name('data-access.index');
            Route::get('/data-access/users', [DataAccessController::class, 'users'])->name('data-access.users');
            Route::get('/data-access/create/{user}', [DataAccessController::class, 'create'])->name('data-access.create');
            Route::post('/data-access', [DataAccessController::class, 'store'])->name('data-access.store');
            Route::get('/data-access/{request}', [DataAccessController::class, 'show'])->name('data-access.show');
            Route::post('/data-access/{dataRequest}/approve', [DataAccessController::class, 'approve'])->name('data-access.approve');
            Route::get('/data-access/{request}/files', [DataAccessController::class, 'viewSharedFiles'])->name('data-access.files');
            Route::get('/files/{file}/download-shared', [FileController::class, 'downloadShared'])->name('files.download-shared');
            Route::get('/digital-signature', [DigitalSignatureController::class, 'index'])
            ->name('digital-signature.index');
        Route::post('/digital-signature/generate-key-pair', [DigitalSignatureController::class, 'generateKeyPair'])
            ->name('generate-key-pair');
        Route::post('/digital-signature/sign-pdf', [DigitalSignatureController::class, 'signPDF'])
            ->name('sign-pdf');
        Route::post('/digital-signature/verify-signature', [DigitalSignatureController::class, 'verifySignature'])
            ->name('verify-signature');
            });
});

require __DIR__.'/auth.php';
