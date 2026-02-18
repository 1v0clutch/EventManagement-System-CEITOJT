<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');

    $query = http_build_query(array_filter([
        'token' => $token,
        'email' => $request->query('email'),
    ]));

    return redirect()->away("{$frontendUrl}/reset-password?{$query}");
})->name('password.reset');
