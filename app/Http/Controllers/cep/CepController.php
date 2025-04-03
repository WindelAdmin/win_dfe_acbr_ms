<?php

namespace App\Http\Controllers\cep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FFI;

class CepController extends Controller
{
    private $ffi;
    private $handle;
    private $initialized = false;

    public function __construct()
    {
        $this->ffi = FFI::cdef("
            int CEP_Inicializar(uintptr_t* libHandle, const char* eArqConfig, const char* eChaveCrypt);
            int CEP_Finalizar(uintptr_t libHandle);
            int CEP_ConfigLerValor(uintptr_t libHandle, const char* eSessao, const char* eChave, char* sValor, long* esTamanho);
            int CEP_ConfigGravarValor(uintptr_t libHandle, const char* eSessao, const char* eChave, const char* eValor);
            int CEP_UltimoRetorno(uintptr_t libHandle, char* sMensagem, long* esTamanho);
            int CEP_BuscarPorCEP(uintptr_t libHandle, const char* eCEP, char* sResposta, long* esTamanho);
        ", __DIR__ . "/libacbrcep64.so");

        $this->handle = FFI::new("uintptr_t[1]");
    }

    private function inicializar($iniPath)
    {
        if ($this->initialized) {
            return null;
        }

        $retorno = $this->ffi->CEP_Inicializar(FFI::addr($this->handle[0]), $iniPath, "");
        if ($retorno !== 0) {
            return $this->ultimoErro("Falha ao inicializar a biblioteca ACBr.");
        }

        $this->initialized = true;
        return null;
    }

    private function finalizar()
    {
        if ($this->initialized) {
            $retorno = $this->ffi->CEP_Finalizar($this->handle[0]);
            if ($retorno !== 0) {
                return ["mensagem" => "Falha ao finalizar a biblioteca ACBr. Código de erro: $retorno"];
            }
            $this->initialized = false;
        }
        return null;
    }

    private function configurarCep($usuario, $senha, $chaveAcesso, $webservice)
    {
        $this->ffi->CEP_ConfigGravarValor($this->handle[0], "CEP", "Usuario", $usuario);
        $this->ffi->CEP_ConfigGravarValor($this->handle[0], "CEP", "Senha", $senha);
        $this->ffi->CEP_ConfigGravarValor($this->handle[0], "CEP", "ChaveAcesso", $chaveAcesso);
        $this->ffi->CEP_ConfigGravarValor($this->handle[0], "CEP", "WebService", $webservice);
    }

    private function buscarPorCep($cep)
    {
        $sResposta = FFI::new("char[16384]");
        $esTamanho = FFI::new("long");
        $esTamanho->cdata = 16384;

        $retorno = $this->ffi->CEP_BuscarPorCEP($this->handle[0], $cep, $sResposta, FFI::addr($esTamanho));

        if ($retorno !== 0) {
            return $this->ultimoErro("Erro ao consultar o CEP.");
        }

        $dados = $this->parseIniString(FFI::string($sResposta));

        return ["resultado" => $dados];
    }

    private function parseIniString($iniString)
    {
        $resultado = [];
        $linhas = explode("\n", $iniString);

        foreach ($linhas as $linha) {
            $linha = trim($linha);

            if ($linha === "" || $linha[0] === "[") {
                continue;
            }

            $partes = explode("=", $linha, 2);
            if (count($partes) == 2) {
                $chave = trim($partes[0]);
                $valor = trim($partes[1]);
                $resultado[$chave] = $valor;
            }
        }

        return $resultado;
    }

    private function ultimoErro($mensagemBase)
    {
        $sMensagem = FFI::new("char[1024]");
        $esTamanho = FFI::new("long");
        $esTamanho->cdata = 1024;
        $this->ffi->CEP_UltimoRetorno($this->handle[0], $sMensagem, FFI::addr($esTamanho));
        return ["mensagem" => "$mensagemBase Detalhe: " . FFI::string($sMensagem)];
    }

    public function consultarCep(Request $request)
    {
        $iniPath = __DIR__ . "/ACBrCEP.ini";

        $erroInicializar = $this->inicializar($iniPath);
        if ($erroInicializar) {
            return response()->json($erroInicializar, 500);
        }

        $this->configurarCep(
            $request->usuario,
            $request->senha,
            $request->chaveacesso,
            $request->webservice
        );

        $resultado = $this->buscarPorCep($request->cep);

        $erroFinalizar = $this->finalizar();
        if ($erroFinalizar) {
            return response()->json($erroFinalizar, 500);
        }

        return response()->json($resultado);
    }
}