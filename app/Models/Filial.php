<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    protected $table = 'filiais';
    protected $fillable = [
        'cliente_id', 
        'cnpj', 
        'localidade'
    ];

}