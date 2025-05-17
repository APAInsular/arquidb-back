<?php

use App\Http\Controllers\Api\v1\AddressController;
use App\Http\Controllers\Api\v1\CenterController;
use App\Http\Controllers\Api\v1\ClientController;
use App\Http\Controllers\Api\v1\CollegiateController;
use App\Http\Controllers\Api\v1\DocumentController;
use App\Http\Controllers\Api\v1\EmailController;
use App\Http\Controllers\Api\v1\ExpedientController;
use App\Http\Controllers\Api\v1\ExpedientHasPeopleController;
use App\Http\Controllers\Api\v1\ExpedientPhasesController;
use App\Http\Controllers\Api\v1\PersonAddressController;
use App\Http\Controllers\Api\v1\PersonClientsController;
use App\Http\Controllers\Api\v1\PersonCollegiatesController;
use App\Http\Controllers\Api\v1\PersonController;
use App\Http\Controllers\Api\v1\PersonEmailsController;
use App\Http\Controllers\Api\v1\PersonPhonesController;
use App\Http\Controllers\Api\v1\PhaseController;
use App\Http\Controllers\Api\v1\PhaseDocumentsController;
use App\Http\Controllers\Api\v1\PhoneController;
use App\Http\Controllers\Api\v1\RecordController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\UserDocumentsController;
use App\Http\Controllers\Api\v1\UserRecordsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\FileController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthenticatedSessionController::class, 'store']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/upload', [FileController::class, 'upload'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('personCollegiate')->group(function () {
        Route::get('/', [PersonCollegiatesController::class, 'index']);
        Route::get('/{id}', [PersonCollegiatesController::class, 'Personcollegiate']);
        Route::post('/', [PersonCollegiatesController::class, 'store']);
        Route::put('/{id}', [PersonCollegiatesController::class, 'update']);
        Route::delete('/{id}', [PersonCollegiatesController::class, 'destroy']);
    });

    Route::prefix('personClient')->group(function () {
        Route::get('/', [PersonClientsController::class, 'index']);
        Route::get('/{id}', [PersonClientsController::class, 'Personclient']);
        Route::post('/', [PersonClientsController::class, 'store']);
        Route::put('/{id}', [PersonClientsController::class, 'update']);
        Route::delete('/{id}', [PersonClientsController::class, 'destroy']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
    });
});

Route::middleware('auth:sanctum')->as('api.')->group(function () {
    // Tablas Generales
    Orion::resource('users', UserController::class);
    Orion::resource('address', AddressController::class);
    Orion::resource('client', ClientController::class);
    Orion::resource('collegiate', CollegiateController::class);
    Orion::resource('document', DocumentController::class);
    Orion::resource('email', EmailController::class);
    Orion::resource('expedient', ExpedientController::class);
    Orion::resource('person', PersonController::class);
    Orion::resource('phase', PhaseController::class);
    Orion::resource('phone', PhoneController::class);
    Orion::resource('record', RecordController::class);
    Orion::resource('centers', CenterController::class);

    // Tablas relacionadas
    Orion::hasManyResource('expedient', 'phases', ExpedientPhasesController::class);
    Orion::hasManyResource('phase', 'documents', PhaseDocumentsController::class);

    Orion::belongsToManyResource('expedient', 'people', ExpedientHasPeopleController::class);
    Orion::hasManyResource('person', 'address', PersonAddressController::class);
    Orion::hasManyResource('person', 'emails', PersonEmailsController::class);
    Orion::hasManyResource('person', 'phones', PersonPhonesController::class);
    Orion::hasManyResource('person', 'clients', PersonClientsController::class);
    Orion::hasManyResource('person', 'collegiates', PersonCollegiatesController::class);

    Orion::hasManyResource('user', 'documents', UserDocumentsController::class);
    Orion::hasManyResource('user', 'records', UserRecordsController::class);

    Route::post('phase/titles', [PhaseController::class, 'titles']);
    Route::post('/import-excel', [ExcelImportController::class, 'import']);
});
