<?php

namespace App\Services;

use App\Repositories\ConversationRepository;
use App\Repositories\ContactRepository;

class webhookService
{

    private $conversationRepository, $contactRepository;

    public function __construct(ConversationRepository $conversationRepository, ContactRepository $contactRepository)
    {
        $this->conversationRepository = $conversationRepository;
        $this->contactRepository = $contactRepository;
    }

    function trata_ack($id_message, $timestamp, $status)
    {
        // atualiza status em histórico de conversas individual (enviada/entregue/lida)
        $data = array("time_" . $status => "$timestamp");

        $conversation = $this->conversationRepository->findByIdMessage($id_message);
        if ($conversation) {
            $this->conversationRepository->update($conversation, $data);
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

    function salva_mensagem($from, $name, $resp, $mensagem, $id_message)
    {
        // Se $resp = 0 :  Mensagem Enviada pelo Chatbot
        // Se $resp = 1 :  Mensagem Recebida do Contato
        // Se $resp = 2 :  Mensagem Enviada pelo Atendente

        //obtem ID do contato
        $id_user = $this->contactRepository->findByTel($from) ? $this->contactRepository->findByTel($from)->id : null ;        

        if (!$id_user) {
            // cadastra contato, caso nao exista
            $contact = array(
                "nome" => $name,
                "telefone" => $from,
                "ultimo_contato" => date('Y-m-d H:i:s')
            );
            $id_user = $this->contactRepository->store($contact);            
        } 

        $data = array (
            "id_contato" => $id_user,
            "resp" => $resp,
            "mensagem" => $mensagem,
            "id_message" => $id_message
        );

        //insere mensagem em Histórico de conversas
        $this->conversationRepository->store($data);

    }
}
