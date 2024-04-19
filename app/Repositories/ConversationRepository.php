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

    public function updateByIdMessage(string $idMessage, array $data)
    {
        $conversation = $this->findByIdMessage($idMessage);   
        if ($conversation) {
            $conversation->update($data);
            $response = "ok";
        } else {
            $response = "Não localizaddo";
        }        
        return $response;
    }
    
}
