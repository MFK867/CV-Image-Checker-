<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CVController;
use App\Http\Controllers\PictureController;

// Home page
Route::get('/', [CVController::class, 'index'])->name('home');

// CV Generator Routes
Route::get('/cv-form', [CVController::class, 'showForm'])->name('cv.form');
Route::post('/cv-generate', [CVController::class, 'generate'])->name('cv.generate');
Route::get('/cv-result/{id}', [CVController::class, 'showResult'])->name('cv.result');

// Picture Validator Routes
Route::get('/picture-validator', [PictureController::class, 'index'])->name('picture.validator');
Route::post('/picture-validate', [PictureController::class, 'validateImage'])->name('picture.validate');
Route::get('/picture-results', [PictureController::class, 'getResults'])->name('picture.results');
Route::get('/picture-download/{filename}', [PictureController::class, 'downloadImage'])->name('picture.download');

// Keep your existing routes for compatibility (optional)
Route::get('/form', [CVController::class, 'showForm'])->name('form');
Route::post('/generate', [CVController::class, 'generate'])->name('generate');
Route::get('/result/{id}', [CVController::class, 'showResult'])->name('result');