<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\webhookService;

class WebhookController extends Controller
{

    private $service;

    public function __construct(webhookService $service)
    {
        $this->service = $service;
    }

    protected function zapWebhook(Request $request)
    {

        $data = $request->json()->all();
        if (!$data) {
            return response()->json(['message' => 'Dados Ausentes'], 400);
        }

        // -------------------------------------------------------------------------
        // tratamento de ACK (status da mensagem: aceita, enviada, entregue,lida)
        // -------------------------------------------------------------------------
        if ( isset($data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"]) ) {
            $id_message = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"];
            $timestamp = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["timestamp"];
            $status = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["status"];

            $response = $this->service->trata_ack($id_message, $timestamp, $status);
        }

        // -------------------------------------------------------------------------
        // tratamento de mensagens recebidas dos usuários
        // -------------------------------------------------------------------------
        if ( isset($data["entry"][0]["changes"][0]["value"]["messages"][0]) ) {

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
            $this->service->salva_mensagem($tel, $name, $resp, $body_message,  $id_message);

            // análise da mensagem recebida
            // $ms->analisa_mensagem($type_message, $body_message, $tel);
            
            $response = array(
                "message" => "ok",
                "status_code" => 200
            );
        }

        return response()->json(['message' => $response["message"]], $response["status_code"]);
    }

}
