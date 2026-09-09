<?php

use App\Http\Controllers\PublicStorageController;
use Illuminate\Support\Facades\Route;

Route::get('/media/{disk}/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('media.public');
