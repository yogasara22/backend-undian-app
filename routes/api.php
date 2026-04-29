<?php

use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\LotteryController;
use App\Http\Controllers\API\ParticipantController;
use App\Http\Controllers\API\PrizeController;
use App\Http\Controllers\API\ScheduledWinnerController;
use App\Http\Controllers\API\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Undian System
|--------------------------------------------------------------------------
| Semua route berada di bawah prefix /api (otomatis dari Laravel)
| sehingga URL lengkapnya: /api/lottery/draw, /api/participants, dst.
|--------------------------------------------------------------------------
*/

// Endpoint Inti: Pengundian
Route::post('/lottery/draw', [LotteryController::class, 'draw']);

// Dashboard & Laporan
Route::get('/dashboard/stats',          [DashboardController::class, 'stats']);
Route::get('/dashboard/recent-winners', [DashboardController::class, 'recentWinners']);

// Management Peserta (full CRUD dengan soft delete)
Route::apiResource('participants', ParticipantController::class);

// Management Kategori (full CRUD)
Route::apiResource('categories', CategoryController::class);

// Management Hadiah (full CRUD + upload image)
Route::apiResource('prizes', PrizeController::class);

// Management Pemenang Terjadwal (antrian Fixed Winner)
Route::get('/scheduled-winners',            [ScheduledWinnerController::class, 'index']);
Route::post('/scheduled-winners',           [ScheduledWinnerController::class, 'store']);
Route::delete('/scheduled-winners/{scheduledWinner}', [ScheduledWinnerController::class, 'destroy']);

// Pengaturan Aplikasi (key-value config)
Route::get('/settings',       [SettingController::class, 'index']);
Route::put('/settings',       [SettingController::class, 'update']);
Route::post('/settings/background-image',   [SettingController::class, 'uploadBackgroundImage']);
Route::delete('/settings/background-image', [SettingController::class, 'deleteBackgroundImage']);
Route::get('/settings/{key}', [SettingController::class, 'show']);