<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API Documentation
Route::get('/api/documentation', function () {
    return view('swagger-ui');
});

// Serve OpenAPI spec
Route::get('/api/openapi.json', function () {
    return response()->file(public_path('openapi.json'), ['Content-Type' => 'application/json']);
});
