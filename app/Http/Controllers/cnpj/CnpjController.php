<?php

namespace App\Http\Controllers\cnpj;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FFI;

class CnpjController extends Controller
{
    private $ffi;
    private $handle;
    private $initialized = false;

    public function __construct()
    {
        $this->ffi = FFI::cdef("
            int CNPJ_Inicializar(uintptr_t* libHandle, const char* eArqConfig, const char* eChaveCrypt);
            int CNPJ_ConfigLerValor(uintptr_t libHandle, const char* eSessao, char* eChave, char* sValor, long* esTamanho);
            int CNPJ_ConfigGravarValor(uintptr_t libHandle, const char* eSessao, const char* eChave, const char* eValor);
            int CNPJ_Consultar(uintptr_t libHandle, const char* eCNPJ, char* sResposta, long* esTamanho);
            int CNPJ_UltimoRetorno(uintptr_t libHandle, char* sMensagem, long* esTamanho);
            int CNPJ_Finalizar(uintptr_t libHandle);
        ", __DIR__ . "/libacbrconsultacnpj64.so");

        $this->handle = FFI::new("uintptr_t[1]");
    }

    private function inicializar($iniPath)
    {
        if ($this->initialized) {
            return null;
        }

        $retorno = $this->ffi->CNPJ_Inicializar(FFI::addr($this->handle[0]), $iniPath, "");
        if ($retorno !== 0) {
            return $this->ultimoErro("Falha ao inicializar a biblioteca ACBr.");
        }

        $this->initialized = true;
        return null;
    }

    private function finalizar()
    {
        if ($this->initialized) {
            $retorno = $this->ffi->CNPJ_Finalizar($this->handle[0]);
            if ($retorno !== 0) {
                return ["mensagem" => "Falha ao finalizar a biblioteca ACBr. Código de erro: $retorno"];
            }
            $this->initialized = false;
        }
        return null;
    }

    private function configurarCnpj($chaveAcesso, $webservice)
    {
        $this->ffi->CNPJ_ConfigGravarValor($this->handle[0], "CNPJ", "ChaveAcesso", $chaveAcesso);
        $this->ffi->CNPJ_ConfigGravarValor($this->handle[0], "CNPJ", "WebService", $webservice);
    }

    private function consultarCnpj($cnpj)
    {
        $sResposta = FFI::new("char[16384]");
        $esTamanho = FFI::new("long");
        $esTamanho->cdata = 16384;

        $retorno = $this->ffi->CNPJ_Consultar($this->handle[0], $cnpj, $sResposta, FFI::addr($esTamanho));

        if ($retorno !== 0) {
            return $this->ultimoErro("Erro ao consultar o CNPJ.");
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
        $this->ffi->CNPJ_UltimoRetorno($this->handle[0], $sMensagem, FFI::addr($esTamanho));
        return ["mensagem" => "$mensagemBase Detalhe: " . FFI::string($sMensagem)];
    }

    public function consultarCnpjApi(Request $request)
    {
        $iniPath = __DIR__ . "/ACBrConsultaCNPJ.ini";

        $erroInicializar = $this->inicializar($iniPath);
        if ($erroInicializar) {
            return response()->json($erroInicializar, 500);
        }

        $this->configurarCnpj($request->chaveacesso, $request->webservice);

        $resultado = $this->consultarCnpj($request->cnpj);

        $erroFinalizar = $this->finalizar();
        if ($erroFinalizar) {
            return response()->json($erroFinalizar, 500);
        }

        return response()->json($resultado);
    }
}
