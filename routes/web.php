<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\PublicationController;

Route::view('/', 'welcome');
Route::post('/upload', [FileUploadController::class, 'upload'])->name('file.upload');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('soumission', 'soumission')
    ->middleware(['auth', 'verified'])
    ->name('soumission');
Route::view('publication', 'publication')
    ->middleware(['auth', 'verified'])
    ->name('publication');
Route::view('mobilite', 'mobilite')
    ->middleware(['auth', 'verified'])
    ->name('mobilite');
Route::view('mobilite-create', 'mobilite-create')
    ->middleware(['auth', 'verified'])
    ->name('mobilite-create');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');


Route::middleware('auth')->group(function () {
    Route::get('/user/publications', [PublicationController::class, 'userPublications'])->name('publications.user');
    Route::get('/publications', [PublicationController::class, 'index'])->name('publications.index');
    Route::get('/publications/{id}', [PublicationController::class, 'show'])->name('publications.show');
    Route::post('/publications', [PublicationController::class, 'store'])->name('publications.store');
    Route::put('/publications/{id}', [PublicationController::class, 'update'])->name('publications.update');
    Route::delete('/publications/{id}', [PublicationController::class, 'destroy'])->name('publications.destroy');
});


require __DIR__ . '/auth.php';
