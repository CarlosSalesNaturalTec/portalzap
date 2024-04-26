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

    public function update(Conversation $conversation, array $data): void
    {
        $conversation->update($data);
    }

    public function store(array $data)
    {
        $this->conversation->firstOrCreate($data);
    }

    public function historic($id_contato, $datetime_limit)
    {
        return $this->conversation
            ->where('id_contato', $id_contato)
            ->where('data_conversa', ">=", $datetime_limit)
            ->get();
    }
    
}
