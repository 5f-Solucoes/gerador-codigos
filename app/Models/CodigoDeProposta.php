<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoDeProposta extends Model
{
    use HasFactory;

    protected $table = 'codigo_de_propostas'; 

    protected $fillable = [
        'codigo_proposta', 
        'data', 
        'descricao', 
        'filial_nome',
        'user_id', 
        'cliente_id', 
        'parceiro_id', 
        'produto_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    //Relacionamento com Cliente 
    public function cliente() {
        return $this->belongsTo(Cliente::class);
    }

    //Relacionamento com Parceiro
    public function parceiro() {
        return $this->belongsTo(Parceiro::class);
    }

    //Relacionamento com Produto
    public function produto() {
        return $this->belongsTo(Produto::class);
    }
}