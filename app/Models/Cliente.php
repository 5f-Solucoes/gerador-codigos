<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
    'nome_fantasia', 
    'razao_social', 
    'cnpj', 
    'site', 
    'cadastrado_por'
    ];

public function filiais() {
    return $this->hasMany(Filial::class);
    }
}
