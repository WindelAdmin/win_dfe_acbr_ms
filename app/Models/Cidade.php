<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    use HasFactory;

    protected $table = 'cidade';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'codigoIBGE',
        'perISS',
        'perDeducaoBaseISS',
        'zonaFranca',
        'agenciaSicredi',
        'instrucoesZonaFranca',
        'paisId',
        'estadoId'
    ];

    protected $casts = [
        'perISS' => 'decimal:2',
        'perDeducaoBaseISS' => 'decimal:2',
        'zonaFranca' => 'boolean',
        'agenciaSicredi' => 'boolean'
    ];

    // Relações
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estadoId');
    }

    // public function pais()
    // {
    //     return $this->belongsTo(Pais::class, 'paisId');
    // }

    // public function docs()
    // {
    //     return $this->hasMany(Doc::class, 'cidade_id');
    // }

    // public function empresas()
    // {
    //     return $this->hasMany(Empresa::class, 'cidade_id');
    // }

    // public function cidadeServico()
    // {
    //     return $this->hasMany(Empresa::class, 'cidadeServico_id');
    // }

    // public function enderecosPessoas()
    // {
    //     return $this->hasMany(EnderecoPessoa::class, 'cidade_id');
    // }

    // public function funcionarios()
    // {
    //     return $this->hasMany(Funcionario::class, 'cidade_id');
    // }

    // public function mdfe()
    // {
    //     return $this->hasMany(Mdfe::class, 'cidade_id');
    // }

    // public function pessoas()
    // {
    //     return $this->hasMany(Pessoa::class, 'cidade_id');
    // }

    // public function servicosCf()
    // {
    //     return $this->hasMany(ServicoCf::class, 'cidade_id');
    // }

    // public function transportadoras()
    // {
    //     return $this->hasMany(Transportadora::class, 'cidade_id');
    // }

    // public function unidadesNegocio()
    // {
    //     return $this->hasMany(UnidadeNegocio::class, 'cidade_id');
    // }

    // public function empresaContador()
    // {
    //     return $this->hasMany(Empresa::class, 'cidadeContador_id');
    // }
}
