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

        // -------------------------------------------------------------------------
        // tratamento de ACK (status da mensagem: aceita, enviada, entregue,lida)
        // -------------------------------------------------------------------------
        if ($data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"]) {
            $id_message = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["id"];
            $timestamp = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["timestamp"];
            $status = $data["entry"][0]["changes"][0]["value"]["statuses"][0]["status"];

            $response = $this->service->trata_ack($id_message, $timestamp, $status);            
        }      
        
        return response()->json(['status' => $response], 200);
        
    }

}
