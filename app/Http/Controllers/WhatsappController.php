<?php

namespace App\Http\Controllers;

use App\Models\Whatsapp;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller 
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Whatsapp $whatsapp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Whatsapp $whatsapp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Whatsapp $whatsapp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Whatsapp $whatsapp)
    {
        //
    }



    public function webhook(Request $request)
    {
        // Parse the request body from the POST
        $body = $request->all();
        $openaiApiKey = env('OPENAI_API_KEY');

        // Check the Incoming webhook message
        // info on WhatsApp text message payload: https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks/payload-examples#text-messages
        if (isset($body['object'])) {
            if (isset($body['entry']) && isset($body['entry'][0]['changes']) && isset($body['entry'][0]['changes'][0]['value']['messages']) && isset($body['entry'][0]['changes'][0]['value']['messages'][0])) {
                $phone_number_id = $body['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];
                $from = $body['entry'][0]['changes'][0]['value']['messages'][0]['from']; // extract the phone number from the webhook payload
                $msg_body = $body['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']; // extract the message text from the webhook payload

                $data = array(
                    'model' => 'text-davinci-003', // Especifica el modelo de OpenAI que se utilizará para generar el texto
                    'prompt' => $msg_body, // Especifica el fragmento de texto que se usará como entrada para generar el texto
                    'max_tokens' => 2100, // Especifica el número máximo de "tokens" (palabras o caracteres) que se generarán en la respuesta
                    'temperature' => 0.5 // Especifica el nivel de "temperatura" para el modelo (0 = sin aleatoriedad, 1 = completamente aleatorio)
                );
                        $payload = json_encode($data);
                        $ch = curl_init('https://api.openai.com/v1/completions');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '.$openaiApiKey
                        ));
                        $result = curl_exec($ch);
                        curl_close($ch);
                        $resultdeco = json_decode($result);
                        $text1 = $resultdeco->choices[0]->text;


                        
                $client = new Client();
                $response = $client->post('https://graph.facebook.com/v12.0/' . $phone_number_id . '/messages?access_token=' . env('WHATSAPP_TOKEN'), [
                    'json' => [
                        'messaging_product' => 'whatsapp',
                        'to' => $from,
                        'text' => ['body' => 'Ack: ' . $text1],
                    ],
                ]);
            }
            return response('Success', 200);
        } else {
            // Return a '404 Not Found' if event is not from a WhatsApp API
            return response('Not Found', 404);
        }
    }

    public function verify(Request $request)
    {
        /**
         * UPDATE YOUR VERIFY TOKEN
         *This will be the Verify Token value when you set up webhook
        **/
        $verify_token = env('VERIFY_TOKEN');

        // Parse params from the webhook verification request
        $mode = $request->hub_mode;
        $token = $request->hub_verify_token;
        $challenge = $request->hub_challenge;
        // Check if a token and mode were sent
        
        if ($mode && $token) {
            // Check the mode and token sent are correct
            if ($mode === "subscribe" && $token === $verify_token) {
                // Respond with 200 OK and challenge token from the request
                return response($challenge, 200);
            } else {
                // Responds with '403 Forbidden' if verify tokens do not match
                return response('Forbidden', 403);
            }
        }
    }
}
