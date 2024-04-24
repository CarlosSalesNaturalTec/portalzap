<?php

namespace App\Repositories;

use App\Models\Conversation;

class ConversationRepository 
{
    private $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function findByIdMessage(string $idMessage)
    {
        return $this->conversation->where('id_message', $idMessage)->first();
    }

    public function updateByIdMessage(string $idMessage, array $data): array
    {
        $conversation = $this->findByIdMessage($idMessage);   
        if ($conversation) {
            $conversation->update($data);
            $message = "ok";
            $status_code = 200;
        } else {
            $message = "ID da mensagem não localizaddo";
            $status_code = 404;
        }    

        return array(
            "message" => $message,
            "status_code" => $status_code
        ); 
    }
    
}
