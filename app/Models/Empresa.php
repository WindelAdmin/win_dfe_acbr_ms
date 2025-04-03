<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresa';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'situacao', 'nome', 'nomefantasia', 'tipoFJ', 'cpfCnpj', 'inscricaoEstadual',
        'inscricaoMunicipal', 'cnaePrimario', 'cnaeSecundario', 'telefone', 'celular',
        'email', 'homePage', 'contato', 'rua', 'numero', 'bairro', 'cep', 'complemento',
        'cidadeId', 'estadoId', 'logomarca', 'certificadoDigitalBase64', 'certificadoDigitalNome',
        'certificadoDigitalSenha', 'certificadoDigitalIdApiDFe', 'tokenApiDFeHomologacao',
        'tokenApiDFeProducao', 'ambienteNfe', 'ambienteCte', 'aliqSimplesNacional', 'licencausoNfe',
        'repositorioXmlNfe', 'nfeConjugada', 'ambienteNfce', 'licencausoNfce', 'repositorioXmlNfc',
        'tipoImpressaoNfce', 'tipoImpressaoCte', 'impressaoecologica', 'codigoCsc',
        'regimeEspecialTribNfce', 'ambienteNfse', 'licencausoNfSe', 'repositorioXmlNfSe',
        'metodoEnvioNfse', 'regimeEspecialTribNfSe', 'codigoTribMunicipioNfse',
        'descicaoTribMunicipioNFse', 'intervaloTempoloteNfse', 'usuariosSimultaneos',
        'cotaDfe', 'usuarioWebServiceNfse', 'senhaWebServiceNfse', 'nomeContador',
        'CrcContador', 'nomeFantasiaContador', 'tipoFJContador', 'cpfCnpjContador',
        'inscEstadualContador', 'inscMunicipalContador', 'emailContador', 'telefoneContador',
        'celularContador', 'ruaContador', 'numeroContador', 'bairroContador', 'cepContador',
        'complementoContador', 'cpfContador', 'cidadeContadorId', 'estadoContadorId',
        'regimeTributario', 'idDfeApi', 'idCsc', 'naturezaOperacaoPadraoVendaId',
        'transportadoraPadraoVendaId', 'condicaoPagamentoPadraoVendaId', 'formaPagamentoPadraoVendaId',
        'planoContaPadraoVendaId', 'tipoDeFrete', 'tipoIss', 'tipoInss', 'tipoPresencaComprador',
        'destinoOperacao', 'impressaoDanfe', 'nuvemFiscal', 'dataValidadeTeste', 'versaoTeste',
        'aceitoTermos', 'liberaCadastros', 'liberaCompras', 'liberaDashboard', 'liberaFinanceiro',
        'liberaRelatorios', 'liberaVendas', 'inadimplente', 'padraoNFSE', 'incentivoFiscal',
        'opSimpNac', 'regApTribSN', 'regEspTrib', 'centroDeCustoPadraoCompraId',
        'naturezaOperacaoPadraoCompraId', 'planoContaPadraoCompraId', 'ambienteMdfe', 'outrosSetores',
        'segmento', 'setor', 'unidadesNegocioId', 'naturezaOperacaoPadraoVendaNfceId',
        'tokenWebServiceNfse', 'cidadeServicoId', 'contaCaixa', 'contaContabilCliente',
        'contaContabilFornecedor', 'larguraDanfce', 'rntrc', 'planoDeContaPadraoCteId',
        'naturezaOperacaoPadraoCteId', 'classFiscalPadraoCteId', 'produtoPredominanteCteId',
        'empresaPaiId'
    ];

    protected $casts = [
        'situacao' => 'boolean',
        'nfeConjugada' => 'boolean',
        'impressaoecologica' => 'boolean',
        'nuvemFiscal' => 'boolean',
        'versaoTeste' => 'boolean',
        'aceitoTermos' => 'boolean',
        'liberaCadastros' => 'boolean',
        'liberaCompras' => 'boolean',
        'liberaDashboard' => 'boolean',
        'liberaFinanceiro' => 'boolean',
        'liberaRelatorios' => 'boolean',
        'liberaVendas' => 'boolean',
        'inadimplente' => 'boolean',
        'incentivoFiscal' => 'boolean',
        'aliqSimplesNacional' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dataValidadeTeste' => 'datetime'
    ];

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidadeId');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estadoId');
    }

    public function empresaPai()
    {
        return $this->belongsTo(Empresa::class, 'empresaPaiId');
    }

    public function filiais()
    {
        return $this->hasMany(Empresa::class, 'empresaPaiId');
    }
}
