<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_conversa',   
        'resp',
        'id_contato',
        'id_promo',
        'mensagem',
        'id_message',
        'time_sent',
        'time_delivered',
        'time_read'
    ];

    protected $dates = [
        'data_conversa'
    ];
}
