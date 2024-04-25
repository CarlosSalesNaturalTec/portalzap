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
        if (!$data) { return response()->json(['message' => 'Dados Ausentes'], 400); }

        $response = $this->service->analisa_request($data);
        return response()->json(['message' => $response["message"]], $response["status_code"]);
    }

}
