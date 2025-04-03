<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $table = 'estado';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'UF',
        'codigoIBGE',
        'perAliqInter',
        'regiaoId',
        'tipoDifal',
        'horasPrazoCancelamentoNFe',
        'aliqGeralInterna',
        'aliqInterestadual',
        'aliqGeralInternaCte'
    ];

    public function cidades()
    {
        return $this->hasMany(Cidade::class, 'estadoId');
    }

    // public function dadosReboquesMDFE()
    // {
    //     return $this->hasMany(DadosReboqueMDFE::class, 'estadoId');
    // }

    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'estadoId');
    }

    // public function empresaIests()
    // {
    //     return $this->hasMany(EmpresaIest::class, 'estadoId');
    // }

    // public function enderecosPessoas()
    // {
    //     return $this->hasMany(EnderecosPessoa::class, 'estadoId');
    // }

    // public function funcionarios()
    // {
    //     return $this->hasMany(Funcionario::class, 'estadoId');
    // }

    // public function ncms()
    // {
    //     return $this->hasMany(Ncm::class, 'estadoId');
    // }

    // public function pessoas()
    // {
    //     return $this->hasMany(Pessoa::class, 'estadoId');
    // }

    // public function transportadoras()
    // {
    //     return $this->hasMany(Transportadora::class, 'estadoId');
    // }

    // public function ufPercursoMDFEs()
    // {
    //     return $this->hasMany(UfPercursoMDFE::class, 'estadoId');
    // }

    // public function unidadesNegocio()
    // {
    //     return $this->hasMany(UnidadesNegocio::class, 'estadoId');
    // }

    // public function empresaContadores()
    // {
    //     return $this->hasMany(Empresa::class, 'estadoId')->wherePivot('relation', 'estadoContador');
    // }
}
