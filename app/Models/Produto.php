<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
    'nome', 'sigla', 'parceiro_id', 'cadastrado_por'
    ];
}
