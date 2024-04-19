<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',   
        'telefone',
        'grupo',
        'sexo',
        'nascimento',
        'endereco',
        'bairro',
        'cidade',
        'status',
        'id_promo',
        'necessita_atendimento',
        'ultimo_contato'
    ];

    protected $dates = [
        'nascimento',
        'ultimo_contato'
    ];
}
