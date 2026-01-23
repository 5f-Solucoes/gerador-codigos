<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoDeProposta extends Model
{
    protected $table = 'codigo_de_propostas'; // Forçar o nome caso o plural esteja diferente
    protected $fillable = [
    'codigo_proposta', 'data', 'descricao', 'filial_nome',
    'user_id', 'cliente_id', 'parceiro_id', 'produto_id',
    'distribuidor_id', 'fornecedor_id'
    ];
}
