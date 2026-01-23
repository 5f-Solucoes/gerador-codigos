<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parceiro extends Model
{
    protected $fillable = [
    'nome', 'sigla', 'cadastrado_por'
    ];
}
