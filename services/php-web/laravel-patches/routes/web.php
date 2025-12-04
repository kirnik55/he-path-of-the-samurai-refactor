<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IssController;
use App\Http\Controllers\OsdrController;
use App\Http\Controllers\AstroController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\ProxyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Здесь только обычные веб-страницы и простые API-эндпоинты.
| Без всяких middleware, чтобы сейчас всё стабильно заработало.
|
*/

// Главная панель
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ISS — отдельная страница
Route::get('/iss', [IssController::class, 'index'])->name('iss');

// OSDR — отдельная страница
Route::get('/osdr', [OsdrController::class, 'index'])->name('osdr');

// JWST — отдельная страница
Route::get('/jwst', [DashboardController::class, 'jwstPage'])->name('jwst');

// Astro — отдельная страница
Route::get('/astro', [AstroController::class, 'page'])->name('astro');

// CMS-демо
Route::get('/cms/welcome', [CmsController::class, 'welcome']);
Route::get('/cms/unsafe', [CmsController::class, 'unsafe']);
Route::get('/cms/dashboard-experiment', [CmsController::class, 'dashboardExperiment']);

// ==== API ====

// ISS API
Route::prefix('api/iss')->group(function () {
    Route::get('last',  [ProxyController::class, 'last']);
    Route::get('trend', [ProxyController::class, 'trend']);
});

// JWST API
Route::get('api/jwst/feed', [DashboardController::class, 'jwstFeed']);

// Astronomy API
Route::get('api/astro/events', [AstroController::class, 'events']);
