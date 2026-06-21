<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Backend radi – koristi /api rute za pristup podacima.'
    ], 200);
});

