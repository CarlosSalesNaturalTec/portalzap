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

    function analisa_request(array $data)
    {
        
        // -------------------------------------------------------------------------
        // tratamento de ACK (status da mensagem: aceita, enviada, entregue,lida)
        // -------------------------------------------------------------------------
        if ( isset($data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"]) ) 
        {
            $id_message = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"];
            $timestamp = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["timestamp"];
            $status = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["status"];

            $response = $this->trata_ack($id_message, $timestamp, $status);
        }

        // -------------------------------------------------------------------------
        // tratamento de mensagens recebidas dos usuários
        // -------------------------------------------------------------------------
        if ( isset($data["entry"][0]["changes"][0]["value"]["messages"][0]) ) 
        {
            $id_message = $data["entry"][0]["changes"][0]["value"]["messages"][0]["id"];
            $tel = $data["entry"][0]["changes"][0]["value"]["messages"][0]["from"];
            $timestamp  = $data["entry"][0]["changes"][0]["value"]["messages"][0]["timestamp"];
            $type_message = $data["entry"][0]["changes"][0]["value"]["messages"][0]["type"];
            $name = $data["entry"][0]["changes"][0]["value"]["contacts"][0]["profile"]["name"];

            switch ($type_message) {
                case 'text':
                    $body_message = $data["entry"][0]["changes"][0]["value"]["messages"][0]["text"]["body"];
                    break;
                case 'button':
                    $body_message = $data["entry"][0]["changes"][0]["value"]["messages"][0]["button"]["text"];
                    break;
                default:
                    $body_message = "Mensagem do Tipo: " . $type_message;
                    break;
            }

            // salva em histórico de mensagens recebidas 
            $resp = 1;  // $resp = 1 :  Mensagem Recebida do Contato           
            $this->salva_mensagem($tel, $name, $resp, $body_message,  $id_message);

            // análise da mensagem recebida
            // $ms->analisa_mensagem($type_message, $body_message, $tel);
            
            $response = array(
                "message" => "ok",
                "status_code" => 200
            );
        }

        return $response;
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
