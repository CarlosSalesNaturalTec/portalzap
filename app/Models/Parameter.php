<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_telefone',   
        'id_waba',
        'id_aplicativo',
        'api_url',
        'token',
        'url_prompt',
    ];
}
