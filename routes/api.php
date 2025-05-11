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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\FileController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthenticatedSessionController::class, 'store']);
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/upload', [FileController::class, 'upload'])->middleware('auth:sanctum');

Route::get('/personCollegiate', [PersonCollegiatesController::class, 'index'])->middleware('auth:sanctum');
Route::get('/personCollegiate/{id}', [PersonCollegiatesController::class, 'Personcollegiate']);
Route::post('/personCollegiate', [PersonCollegiatesController::class, 'store']);
Route::put('/personCollegiate/{id}', [PersonCollegiatesController::class, 'update']);
Route::delete('/personCollegiate/{id}', [PersonCollegiatesController::class, 'destroy']);

Route::get('/personClient', [PersonClientsController::class, 'index'])->middleware('auth:sanctum');
Route::get('/personClient/{id}', [PersonClientsController::class, 'Personclient']);
Route::post('/personClient', [PersonClientsController::class, 'store']);
Route::put('/personClient/{id}', [PersonClientsController::class, 'update']);
Route::delete('/personClient/{id}', [PersonClientsController::class, 'destroy']);


Route::group(['as' => 'api.'], function () {

    // Tablas Generales
    Orion::resource('users', UserController::class);
    Orion::resource('address', AddressController::class);
    Orion::resource('client', ClientController::class)->middleware('auth:sanctum');
    Orion::resource('collegiate', CollegiateController::class)->middleware('auth:sanctum');
    Orion::resource('document', DocumentController::class);
    Orion::resource('email', EmailController::class);
    Orion::resource('expedient', ExpedientController::class)->middleware('auth:sanctum');
    Orion::resource('person', PersonController::class)->middleware('auth:sanctum');
    Orion::resource('phase', PhaseController::class)->middleware('auth:sanctum');
    Orion::resource('phone', PhoneController::class);
    Orion::resource('record', RecordController::class);
    Orion::resource('centers', CenterController::class);

    // Tablas relacionadas
    //Relaciones para los expedientes (ademas de optener las personas etc...)

    Orion::hasManyResource('expedient', 'phases', ExpedientPhasesController::class);
    Orion::hasManyResource('phase', 'documents', PhaseDocumentsController::class);

    Orion::belongsToManyResource('expedient', 'people', ExpedientHasPeopleController::class);
    Orion::hasManyResource('person', 'address', PersonAddressController::class);
    Orion::hasManyResource('person', 'emails', PersonEmailsController::class);
    Orion::hasManyResource('person', 'phones', PersonPhonesController::class);

    Orion::hasManyResource('person', 'clients', PersonClientsController::class);
    Orion::hasManyResource('person', 'collegiates', PersonCollegiatesController::class);

    // relaciones del usuario 
    Orion::hasManyResource('user', 'documents', UserDocumentsController::class);
    Orion::hasManyResource('user', 'records', UserRecordsController::class);

    Route::post('phase/titles', [PhaseController::class, 'titles']);
    Route::post('/import-excel', [ExcelImportController::class, 'import']);
});
