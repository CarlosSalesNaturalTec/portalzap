<?php

namespace App\Services;

use App\Repositories\ConversationRepository;
use App\Repositories\ContactRepository;
use App\Repositories\PromotionRepository;
use App\Repositories\AlertRepository;
use App\Repositories\ParameterRepository;

class webhookService
{

    private $conversationRepository, $contactRepository, $promotionRepository, $alertRepository, $parameterRepository;

    public function __construct(ConversationRepository $conversationRepository, ContactRepository $contactRepository, 
        PromotionRepository $promotionRepository, AlertRepository $alertRepository, ParameterRepository $parameterRepository)
    {
        $this->conversationRepository = $conversationRepository;
        $this->contactRepository = $contactRepository;
        $this->promotionRepository = $promotionRepository;
        $this->alertRepository = $alertRepository;
        $this->parameterRepository = $parameterRepository;
    }

    function analisa_request(array $data)
    {

        // -------------------------------------------------------------------------
        // tratamento de ACK (status da mensagem: aceita, enviada, entregue,lida)
        // -------------------------------------------------------------------------
        if (isset($data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"])) {
            $id_message = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"];
            $timestamp = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["timestamp"];
            $status = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["status"];

            $response = $this->trata_ack($id_message, $timestamp, $status);
        }

        // -------------------------------------------------------------------------
        // tratamento de mensagens recebidas dos usuários
        // -------------------------------------------------------------------------
        if (isset($data["entry"][0]["changes"][0]["value"]["messages"][0])) {
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

            // análise da mensagem recebida E envio de resposta
            $this->analisa_mensagem($type_message, $body_message, $tel);            

            $response =  array(
                "message" => "ok",
                "status_code" => 200
            );
        }

        // -------------------------------------------------------------------------
        // Atualização de Status de Modelos 
        // -------------------------------------------------------------------------
        if ( isset($data["entry"][0]["changes"][0]["field"]) ) {
            if ( $data["entry"][0]["changes"][0]["field"] == "message_template_status_update" ) {
                $event =  $data["entry"][0]["changes"][0]["value"]["event"];     // status
                $message_template_id =  $data["entry"][0]["changes"][0]["value"]["message_template_id"];

                switch ($event) {
                    case 'APPROVED':
                        $obs = "";
                        break;
                    case 'REJECTED':
                        $obs = "Modelo rejeitado. Motivo: " . $data["entry"][0]["changes"][0]["value"]["reason"];
                        break;
                    case 'FLAGGED':
                        $obs = "Desabilitação de modelo agendada para: " . $data["entry"][0]["changes"][0]["value"]["disable_info"]["disable_date"];
                        break;
                    case 'PAUSED':
                        $title =  $data["entry"][0]["changes"][0]["value"]["other_info"]["title"];
                        $description =  $data["entry"][0]["changes"][0]["value"]["other_info"]["description"];
                        $obs = "Modelo Pausado. " . $title . " - " . $description;
                        break;
                    case 'PENDING_DELETION':
                        $obs = "Exclusão de Modelo";
                        break;
                    default:
                        $obs="Verifique Status do Modelo junto ao Meta";
                        break;
                }

                $response = $this->atualiza_status_modelos($event, $message_template_id, $obs);
            }            
        }

        // -------------------------------------------------------------------------
        // Atualização de Score de Qualidade de Modelos 
        // -------------------------------------------------------------------------
        if (isset($data["entry"][0]["changes"][0]["field"])) {
            if ($data["entry"][0]["changes"][0]["field"] == "message_template_quality_update") {
                $previus = $data["entry"][0]["changes"][0]["value"]["previous_quality_score"];     
                $new = $data["entry"][0]["changes"][0]["value"]["new_quality_score"];  
                $message_template_id = $data["entry"][0]["changes"][0]["value"]["message_template_id"]; 
                $message_template_name = $data["entry"][0]["changes"][0]["value"]["message_template_name"];
    
                $response = $this->atualiza_score_qualidade_modelos($previus, $new, $message_template_id, $message_template_name);
            }     
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
        $id_user = $this->contactRepository->findByTel($from) ? $this->contactRepository->findByTel($from)->id : null;

        if (!$id_user) {
            // cadastra contato, caso nao exista
            $contact = array(
                "nome" => $name,
                "telefone" => $from,
                "ultimo_contato" => date('Y-m-d H:i:s')
            );
            $id_user = $this->contactRepository->store($contact);
        }

        $data = array(
            "id_contato" => $id_user,
            "resp" => $resp,
            "mensagem" => $mensagem,
            "id_message" => $id_message
        );

        //insere mensagem em Histórico de conversas
        $this->conversationRepository->store($data);
    }

    function atualiza_status_modelos($event, $message_template_id, $obs) {
                     
        $promotion = $this->promotionRepository->findByIdModel($message_template_id);
        if (!$promotion) {           
            return array(
                "message" => "ID do Modelo não localizaddo",
                "status_code" => 404
            );
        }

        // Atualiza cadastro de Modelo/Promocao
        $data = array(
            "status" => $event,
            "obs" => $obs
        );
        $this->promotionRepository->update($promotion, $data);

        // Salva Alerta (se houver)
        if ($event != "APPROVED") {
            $data = array (
                "alerta" => "Mudança de Status da campanha $promotion->promo. Novo Status: $obs' ",
            );
            $this->alertRepository->store($data);                        
        }

        return array(
            "message" => "Ok",
            "status_code" => 200
        );
        
    }

    function atualiza_score_qualidade_modelos($previus, $new, $message_template_id, $message_template_name){
        
        $promotion = $this->promotionRepository->findByIdModel($message_template_id);
        if (!$promotion) {           
            return array(
                "message" => "ID do Modelo não localizaddo",
                "status_code" => 404
            );
        }

        $data = array(
            "score_qualidade_atual" => $new,
            "score_qualidade_anterior" => $previus,
        );        
        $this->promotionRepository->update($promotion, $data);

        $data = array (
            "alerta" => "Mudança de qualidade da campanha $promotion->promo. Novo score: $new' ",
        );
        $this->alertRepository->store($data);       
       
        $response = array(
            "message" => "ok",
            "status_code" => 200
        );
        
        return $response;
    }

    function analisa_mensagem ($type_message, $user_message, $tel) 
    {
        // Comando para Parar Envio de Mensagens / Inativar Cadastro
        if ( strtoupper($user_message) == "#PARARMENSAGENS") {
            $this->inativar_contato($tel); // parei aqui
            $this->atualiza_quants_campanha($tel, "C");  // Atualiza quantidade de cancelamentos da campanha
            exit;
        }

        // Comando para Reiniciar Envio de Mensagens / Reativar Cadastro
        if ( strtoupper($user_message)  == "#ATIVARCADASTRO") {
            $this->ativar_contato($tel);
            exit;
        }

        // dados do contato
        $contact = $this->contactRepository->findByTel($tel);
        if ($contact) {
            $nome = $contact->nome;
            $id_contato = $contact->id;
            if ($contact->status == "Inativos") {exit;}                      
        }                                          
        
        // obtem PROMPT a partir de arquivo TXT armazenado em nuvem 
        $id_cli = 1;
        $param = $this->parameterRepository->findById($id_cli);
        $url_prompt = $param->url_prompt;
        $prompt = file_get_contents($url_prompt, false);       
        
        // montagem do corpo da requisição
        $history = []; $parts_user = []; $parts_model = [];
        array_push($parts_user, ["text" => "Eu me chamo $nome"] );
        array_push($parts_model, [ "text" => $prompt ] );

        // histórico de mensagens (última hora)
        $currentTime = date('Y-m-d H:i:s');
        $timestamp = strtotime($currentTime);
        $oneHourLess = $timestamp - (3600*4); // 3600 seconds in one hour * 4. Sendo que 3 horas do fuso horário + 1 hora atrás
        $datetime_limit = date('Y-m-d H:i:s', $oneHourLess);

        $conversations = $this->conversationRepository->historic($id_contato, $datetime_limit);
        foreach ($conversations as $conversation) {
            if ($conversation->resp == "1") {
                array_push($parts_user, ["text" => $conversation->mensagem ]);
            } elseif ($conversation->resp == "0") {
                array_push($parts_model, [ "text" => $conversation->mensagem ] );
            }
        }
        
        array_push($history, [
                "role" => "user",
                "parts" => $parts_user,
            ]
        );

        array_push($history, [
                "role" => "model",
                "parts" => $parts_model,
            ]
        );

        $dados = array(
            'user_message' => "$user_message",
            'history' => $history,
        );
        
        // Envia mensagem para análise de Inteligência Artificial (Requisição para servidor Node.JS hospedado na Google CLoud / Serviço: GEMINI AI)
        $url = 'https://geminiaiapi-6qbxp7kiba-uw.a.run.app/chat';         
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        )); 
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $IA_response = "Olá, este é um atendimento automático. Breve entraremos em contato.";
        } else {
            $IA_response = $result;  
        }
        curl_close($ch);
        
        // envia mensagem automática de texto
        $this->envia_msg_texto($tel, $IA_response);                        
    }

    function envia_msg_texto($tel, $text_response){

        // parâmetros de envio 
        $id_cli = 1;
        $param = $this->parameterRepository->findById($id_cli);

        $url_base = $param->api_url;
        $id_tel = $param->id_telefone;
        $token = $param->token;

        // body request
        $data_text = [
            'preview_url' => false,
            'body' => "$text_response"
        ];
        $data = [
            'messaging_product' => "whatsapp",
            'recipient_type' => "individual",
            'to' => "$tel",
            'type' => "text",
            'text' => $data_text
        ];
        $json = json_encode($data); 

        // cURL request
        $url = $url_base . "/" . $id_tel . "/messages";    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));     
        $response = curl_exec($ch);

        if(curl_errno($ch)) {
            $send_response = 'cURL Error' ; //. curl_error($ch);
        } else {
            $json_response = json_decode($response);        
            if (isset($json_response->messages[0]->id)) {
                $id_message = $json_response->messages[0]->id;
                $send_contact = $json_response->contacts[0]->wa_id;
                $send_response = "OK";
            } else{
                $send_response = "Erro: retorno esperado não recebido.";
            }
        }
        curl_close($ch);
        
        //salva mensagem de texto em histórico de conversas
        $resp = 0; // $resp = 0 :  Mensagem Enviada pelo Chatbot
        $send_name = "";
        
        if ($send_response == "OK") {                  
            $this->salva_mensagem($send_contact, $send_name, $resp, $text_response, $id_message); 
        } else {
            $id_message = "X";
            $this->salva_mensagem($tel, $send_name, $resp, $send_response, $id_message); 
        }

    }         

    function inativar_contato($tel){
        $contact = $this->contactRepository->findByTel($tel);
        if (!$contact) { exit; } 
        
        $data = array( "status" => "Inativos");
        $this->contactRepository->update($contact, $data);
        
        $text_response = "Ok, *não iremos lhe enviar novas mensagens*. Caso tenha solicitado por engano, digite o comando *#ATIVARCADASTRO*.";
        $this->envia_msg_texto($tel, $text_response);
    }

    function ativar_contato($tel){
        $contact = $this->contactRepository->findByTel($tel);
        if (!$contact) { exit; } 

        $data = array( "status" => "Ativos");
        $this->contactRepository->update($contact, $data);      

        $text_response = "Ok, *cadastro ATIVADO*.";
        $this->envia_msg_texto($tel, $text_response);
    }

    function atualiza_quants_campanha($tel, $tipo_quant){

        // obtem dados da última campanha enviada ao usuário        
        $contact = $this->contactRepository->findByTel($tel);
        if (!$contact) { exit; }   
        $id_campanha = $contact->id_promo;                
        
        // atualiza quantidades de cancelamentos ou aceites na tabela de Promocoes/Modelos
        $promotion = $this->promotionRepository->findById($id_campanha);
        if ($tipo_quant == "C") {
            $quant_new = $promotion->quant_cancelamentos + 1;
            $data = array(
                "quant_cancelamentos" => $quant_new
            );
        } else{
            $quant_new = $promotion->quant_aceites + 1;
            $data = array(
                "quant_aceites" => $quant_new
            );
        } 
        $this->promotionRepository->update($promotion, $data);
                
    }

}
