<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OsdrController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\AstroController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\IssController;

// Главная → обзорный дашборд
Route::get('/', fn () => redirect('/dashboard'));

// Обзор
Route::get('/dashboard', [DashboardController::class, 'index']);

// Отдельные бизнес-страницы
Route::get('/iss',  [IssController::class,  'index']);                   // ISS / орбита
Route::get('/osdr', [OsdrController::class, 'index']);                   // OSDR датасеты
Route::get('/jwst', [DashboardController::class, 'jwst']);               // JWST галерея
Route::get('/astro', [AstroController::class, 'page']);                  // AstronomyAPI
Route::get('/cms/dashboard-experiment', [CmsController::class, 'dashboardExperiment']); // CMS демо

// Прокси к rust_iss
Route::get('/api/iss/last',  [ProxyController::class, 'last']);
Route::get('/api/iss/trend', [ProxyController::class, 'trend']);

// Внешние API (JWST, AstronomyAPI)
Route::get('/api/jwst/feed',    [DashboardController::class, 'jwstFeed']);
Route::get('/api/astro/events', [AstroController::class,    'events']);

// CMS-страницы по slug
Route::get('/page/{slug}', [CmsController::class, 'page']);
