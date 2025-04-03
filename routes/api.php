<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\cep\CepController;
use App\Http\Controllers\cnpj\CnpjController;
use App\Http\Controllers\nfe\NfeController;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/consultar-cep', [CepController::class, 'consultarCep']);
Route::post('/consultar-cnpj', [CnpjController::class, 'consultarCnpjApi']);

Route::prefix('nfe')->group(function () {
    Route::post('/distribuicao/ultNSU', [NfeController::class, 'distribuicaoDFePorUltNSU']);
    Route::post('/distribuicao/nsu', [NfeController::class, 'distribuicaoDFePorNSU']);
    Route::post('/distribuicao/chave', [NfeController::class, 'distribuicaoDFePorChave']);
});