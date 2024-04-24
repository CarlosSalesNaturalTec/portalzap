<?php

namespace App\Services;

use App\Repositories\ConversationRepository;

class webhookService
{

    private $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    function trata_ack($id_message, $timestamp, $status)
    {      
        // atualiza status em histórico de conversas individual (enviada/entregue/lida)
        $data = array("time_" . $status => "$timestamp");        
        return $this->conversationRepository->updateByIdMessage($id_message, $data);
    }
}
