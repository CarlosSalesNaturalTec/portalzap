<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'resp',
        'id_contato',
        'id_promo',
        'mensagem',
        'id_message',
        'time_sent',
        'time_delivered',
        'time_read',
        'created_at',
        'updated_at'
    ];

    protected $dates = [
        'data_conversa',
        'created_at',
        'updated_at'
    ];
}
