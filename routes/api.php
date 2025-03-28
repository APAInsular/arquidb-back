<?php

use App\Http\Controllers\Api\v1\AddressController;
use App\Http\Controllers\Api\v1\ClientController;
use App\Http\Controllers\Api\v1\CollegiateController;
use App\Http\Controllers\Api\v1\DocumentController;
use App\Http\Controllers\Api\v1\EmailController;
use App\Http\Controllers\Api\v1\ExpedientController;
use App\Http\Controllers\Api\v1\ExpedientHasPersonsController;
use App\Http\Controllers\Api\v1\ExpedientPhasesController;
use App\Http\Controllers\Api\v1\PersonAddressController;
use App\Http\Controllers\Api\v1\PersonController;
use App\Http\Controllers\Api\v1\PersonEmailsController;
use App\Http\Controllers\Api\v1\PersonPhonesController;
use App\Http\Controllers\Api\v1\PhaseController;
use App\Http\Controllers\Api\v1\PhaseDocumentsController;
use App\Http\Controllers\Api\v1\PhoneController;
use App\Http\Controllers\Api\v1\RecordController;
use App\Http\Controllers\Api\v1\UserDocumentsController;
use App\Http\Controllers\Api\v1\UserRecordsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



Route::group(['as' => 'api.'], function () {

    // Tablas Generales
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

    // Tablas relacionadas
    //Relaciones para los expedientes (ademas de optener las personas etc...)

    Orion::hasManyResource('expedient', 'phases', ExpedientPhasesController::class);
    Orion::hasManyResource('phase', 'documents', PhaseDocumentsController::class);

    Orion::belongsToManyResource('expedient', 'persons', ExpedientHasPersonsController::class);
    Orion::hasManyResource('person', 'address', PersonAddressController::class);
    Orion::hasManyResource('person', 'emails', PersonEmailsController::class);
    Orion::hasManyResource('person', 'phones', PersonPhonesController::class);

    // relaciones del usuario 
    Orion::hasManyResource('user', 'documents', UserDocumentsController::class);
    Orion::hasManyResource('user', 'records', UserRecordsController::class);

});