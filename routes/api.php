<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// AUTHORS
Route::apiResource('authors', AuthorController::class);
Route::get('authors/{id}/books', [AuthorController::class, 'getBooks']);

// BOOKS
Route::apiResource('books', BookController::class);
