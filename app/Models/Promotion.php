<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo',
        'language',
        'tipo_cabecalho',
        'corpo_cabecalho',
        'tipo_rodape',
        'corpo_rodape',
        'tipo_botao1',
        'corpo_botao1',
        'tipo_botao2',
        'corpo_botao2',
        'mensagem',
        'quant_envios',
        'status',
        'categoria',
        'id_modelo',
        'obs',
        'score_qualidade_atual',
        'score_qualidade_anterior',
        'atualizacao_timestamp',
        'status_contato',
        'quant_cancelamentos',
        'quant_aceites',
        'created_at',
        'updated_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
