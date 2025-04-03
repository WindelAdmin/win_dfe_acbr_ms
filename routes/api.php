<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\cep\CepController;
use App\Http\Controllers\cnpj\CnpjController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/teste', function () {
    return response()->json(['mensagem' => 'Rota de API funcionando!']);
});

Route::post('/consultar-cep', [CepController::class, 'consultarCep']);
Route::post('/consultar-cnpj', [CnpjController::class, 'consultarCnpjApi']);
