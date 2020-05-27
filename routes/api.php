<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::post('/{resource}/step/{step}', 'ResourceStoreController@handle');
Route::post('/{resource}/step/{step}/clear', 'ResourceStoreController@clearSession');
Route::get('/{resource}/step/{step}/creation-fields', 'CreationFieldController@index');
Route::get('/{resource}/step/{step}/update-fields', 'UpdateFieldController@index');
