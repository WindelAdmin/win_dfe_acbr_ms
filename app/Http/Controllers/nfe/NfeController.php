<?php

namespace App\Http\Controllers\Nfe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FFI;
use App\Models\Empresa;

class NfeController extends Controller
{
    private $ffi;

    public function __construct()
    {
        $this->ffi = FFI::cdef("
            int NFE_Inicializar(uintptr_t* libHandle, const char* eArqConfig, const char* eChaveCrypt);
            int NFE_DistribuicaoDFePorUltNSU(const uintptr_t libHandle, int AcUFAutor, const char* eCNPJCPF, const char* eultNSU, char* sResposta, long* esTamanho);
            int NFE_DistribuicaoDFePorNSU(const uintptr_t libHandle, int AcUFAutor, const char* eCNPJCPF, const char* eNSU, char* sResposta, long* esTamanho);
            int NFE_DistribuicaoDFePorChave(const uintptr_t libHandle, int AcUFAutor, const char* eCNPJCPF, const char* echNFe, char* sResposta, long* esTamanho);
            int NFE_Finalizar(const uintptr_t libHandle);
            int NFE_UltimoRetorno(const uintptr_t libHandle, char* sMensagem, long* esTamanho);
            int NFE_ConfigGravarValor(const uintptr_t libHandle, const char* eSessao, const char* eChave, const char* eValor);
        ", __DIR__ . "/libacbrnfe64.so");
    }

    private function inicializar(Empresa $empresa)
    {
        $handle = FFI::new("uintptr_t[1]");

        $certPath = sys_get_temp_dir() . "/cert_" . uniqid() . ".pfx";
        file_put_contents($certPath, base64_decode($empresa->certificadoDigitalBase64));

        $iniPath = sys_get_temp_dir() . "/acbrnfe_" . uniqid() . ".ini";
        file_put_contents($iniPath, $this->gerarIniConfig($certPath, $empresa->certificadoDigitalSenha, $empresa->estado->UF));

        $retorno = $this->ffi->NFE_Inicializar(FFI::addr($handle[0]), $iniPath, "");

        if ($retorno !== 0) {
            $this->finalizar($handle, $iniPath, $certPath);
            return [null, null, null, "Erro ao inicializar ACBr"];
        }

        $erros = [];
        $configuracoes = [
            ["DFe", "UF", $empresa->estado->UF],
            ["DFe", "ArquivoPFX", $certPath],
            ["DFe", "Senha", $empresa->certificadoDigitalSenha],
        ];

        foreach ($configuracoes as [$sessao, $chave, $valor]) {
            $resultado = $this->configGravarValor($handle, $sessao, $chave, $valor);
            if (isset($resultado['erro'])) {
                $erros[] = $resultado['erro'];
            }
        }

        if (!empty($erros)) {
            $this->finalizar($handle, $iniPath, $certPath);
            return [null, null, null, "Erro ao configurar INI: " . implode("; ", $erros)];
        }

        return [$handle, $iniPath, $certPath, null];
    }

    private function gerarIniConfig($certPath, $senha, $uf)
    {
        $iniTemplatePath = __DIR__ . "/ACBrNFe.ini";
        if (!file_exists($iniTemplatePath)) {
            throw new \Exception("Arquivo INI de template não encontrado em: " . $iniTemplatePath);
        }

        $iniContent = file_get_contents($iniTemplatePath);

        return $iniContent;
    }

    private function configGravarValor($handle, $eSessao, $eChave, $valor)
    {
        $retorno = $this->ffi->NFE_ConfigGravarValor($handle[0], $eSessao, $eChave, $valor);

        if ($retorno !== 0) {
            $sMensagem = FFI::new("char[535]");
            $this->ultimoRetorno($handle, $retorno, $sMensagem, "Erro ao gravar valores [$valor] na seção [$eSessao] e chave [$eChave]");
            return ["erro" => trim(FFI::string($sMensagem))];
        }

        return ["sucesso" => true];
    }

    public function distribuicaoDFePorUltNSU(Request $request)
    {
        return $this->executarDistribuicao('NFE_DistribuicaoDFePorUltNSU', $request);
    }

    public function distribuicaoDFePorNSU(Request $request)
    {
        return $this->executarDistribuicao('NFE_DistribuicaoDFePorNSU', $request);
    }

    public function distribuicaoDFePorChave(Request $request)
    {
        return $this->executarDistribuicao('NFE_DistribuicaoDFePorChave', $request);
    }

    private function executarDistribuicao($metodo, Request $request)
    {
        $empresaId = $request->input('empresaId');
        $empresa = Empresa::find($empresaId);
        if (!$empresa) {
            return response()->json(['error' => 'Empresa não encontrada'], 404);
        }

        [$handle, $iniPath, $certPath, $erro] = $this->inicializar($empresa);
        if ($erro) {
            return response()->json(['error' => $erro], 500);
        }

        $respostaBuffer = FFI::new("char[8192]");
        $tamanho = FFI::new("long");

        $codigoRetorno = $this->ffi->$metodo(
            $handle[0],
            $empresa->estado->UF,
            $empresa->cpfCnpj,
            '0',
            $respostaBuffer,
            FFI::addr($tamanho)
        );

        if ($codigoRetorno !== 0) {
            $erroDetalhado = $this->ultimoRetorno($handle, $codigoRetorno, "Erro ao executar $metodo");

            $this->finalizar($handle, $iniPath, $certPath);

            return $erroDetalhado ?: response()->json([
                'error' => "Erro ao executar $metodo. Código de erro: $codigoRetorno"
            ], 400);
        }

        $mensagemRetorno = $this->capturarUltimoRetorno($handle);

        $this->finalizar($handle, $iniPath, $certPath);

        return response()->json([
            'sucesso' => true
        ] + $mensagemRetorno);
    }


    private function capturarUltimoRetorno($handle)
    {
        $tamanho = FFI::new("long");
        $tamanho->cdata = 8192;

        $mensagemBuffer = FFI::new("char[8192]");

        $this->ffi->NFE_UltimoRetorno($handle[0], $mensagemBuffer, FFI::addr($tamanho));

        $retorno = trim(FFI::string($mensagemBuffer, $tamanho->cdata));

        $dados = json_decode($retorno, true);
var_dump($dados);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                "erro" => "Falha ao interpretar resposta da ACBrLib.",
                "raw" => $retorno
            ];
        }

        if (isset($dados['DistribuicaoDFe'])) {
            $distribuicao = $dados['DistribuicaoDFe'];

            if (isset($distribuicao['loteDistDFeInt']) && is_array($distribuicao['loteDistDFeInt'])) {
                return [
                    "total_documentos" => count($distribuicao['loteDistDFeInt']),
                    "documentos" => $distribuicao['loteDistDFeInt']
                ];
            }

            return [
                "dados" => $distribuicao
            ];
        }

        return [
            "dados" => $dados
        ];
    }


    private function finalizar($handle, $iniPath, $certPath)
    {
        if ($handle[0]) {
            $this->ffi->NFE_Finalizar($handle[0]);
        }

        if (file_exists($iniPath)) {
            unlink($iniPath);
        }

        if (file_exists($certPath)) {
            unlink($certPath);
        }
    }

    private function ultimoRetorno($handle, $retornolib, $msgErro, $retMensagem = 0)
    {
        if ($retornolib !== 0 || $retMensagem == 1) {
            $esTamanho = FFI::new("long");
            $esTamanho->cdata = 9048;

            $sMensagem = FFI::new("char[9048]");

            $this->ffi->NFE_UltimoRetorno($handle[0], $sMensagem, FFI::addr($esTamanho));

            $ultimoRetorno = trim(FFI::string($sMensagem, $esTamanho->cdata));

            if (preg_match('/Data de Validade do Certificado já expirou: (\d{2}\/\d{2}\/\d{4})/', $ultimoRetorno, $matches)) {
                $ultimoRetorno = "Data de Validade do Certificado já expirou: " . $matches[1];
            }

            $mensagemFormatada = [
                "erro" => $msgErro,
                "codigo" => $retornolib,
                "mensagem" => $ultimoRetorno
            ];

            if (str_contains($ultimoRetorno, "Erro ao ler informações do Certificado")) {
                $mensagemFormatada["possivel_causa"] = "Senha incorreta no certificado digital.";
            } elseif (str_contains($ultimoRetorno, "Falha de comunicação")) {
                $mensagemFormatada["possivel_causa"] = "Problema ao conectar com a SEFAZ.";
            } elseif (str_contains($ultimoRetorno, "Data de Validade do Certificado já expirou")) {
                $mensagemFormatada["possivel_causa"] = "O certificado digital está vencido. Renove-o para continuar.";
            }

            return response()->json($mensagemFormatada, 400);
        }

        return null;
    }
}
