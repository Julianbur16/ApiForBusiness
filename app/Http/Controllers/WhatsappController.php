<?php

namespace App\Http\Controllers;

use App\Models\Whatsapp;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\ProductController;
use CURLFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Psr7\Utils;


class WhatsappController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $whatsapps = whatsapp::all();
        return response()->json($whatsapps);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function sendtemplate(Request $request)
    {
        $clave = env('CLAVE_SEND');
        $phone_number_id = env('ID_PHONE');
        if ($request->clave == $clave) {
            $client = new Client();
            $response = $client->post('https://graph.facebook.com/v12.0/' . $phone_number_id . '/messages?access_token=' . env('WHATSAPP_TOKEN'), [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $request->from,
                    "type" => "template",
                    "template" => [
                        "name" => "inicio",
                        "language" => [
                            "code" => "es"
                        ],
                        "components" => [
                            [
                                "type" => "body",
                                "parameters" => [
                                    [
                                        "type" => "text",
                                        "text" => "¿Este chat funciona por medio de Chatgpt?"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $request->q1
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $request->q2
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]);

            $data = ["message" => "Enviado satisfactoriamente"];
            return response()->json($data);
        } else {

            $data = ["message" => "Error de credenciales"];
            return response()->json($data);
        }
    }

    /*
     * Store a newly created resource in storage.
     * "messaging_product": "whatsapp",
    "to": "{{Recipient-Phone-Number}}",
    "type": "template",
    "template": {
        "name": "hello_world",
        "language": {
            "code": "en_US"
        }
    }
     */
    public function store(Request $request)
    {
        /*
        $table->string('Phone')->unique();
            $table->string('Profession')->nullable();
            $table->string('Name')->nullable();
            $table->string('City')->nullable();
        */
        $whatsapps = new Whatsapp();
        $whatsapps->Phone = $request->Phone;
        $whatsapps->Profession = $request->Profession;
        $whatsapps->Name = $request->Name;
        $whatsapps->City = $request->City;
        $whatsapps->save();
        return response()->json($whatsapps);
    }

    /**
     * Display the specified resource.
     */
    public function show(Whatsapp $whatsapp)
    {
        $whatsapps = whatsapp::find($whatsapp);
        if ($whatsapps != null) {
            return response()->json($whatsapps);
        } else {
            $data = ["message" => "cliente no existe"];
            return response()->json($data);
        }
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
        /* 
        $table->string('Phone')->unique();
            $table->string('Profession')->nullable();
            $table->string('Name')->nullable();
            $table->string('City')->nullable();
        */
        $whatsapp->Phone = $request->Phone;
        $whatsapp->Profession = $request->Profession;
        $whatsapp->Name = $request->Name;
        $whatsapp->City = $request->City;
        $whatsapp->save();
        $data = [
            "message" => "client update successfully",
            "client" => "$whatsapp"
        ];

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Whatsapp $whatsapp)
    {
        $whatsapps = whatsapp::find($whatsapp->id);
        $whatsapps->delete();
        $data = ["message" => "client delete successfully"];
        return response()->json($data);
    }

    public function enviarmsm(string $phone_number_id, string $from, string $text)
    {
        $client = new Client();
        $response = $client->post('https://graph.facebook.com/v12.0/' . $phone_number_id . '/messages?access_token=' . env('WHATSAPP_TOKEN'), [
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'text' => ['body' => $text],
            ],
        ]);
    }

    public function responsechat($promt, $msg, $from)
    {
        $openaiApiKey = env('OPENAI_API_KEY');
        $messages = cache($from, []);

        if (empty($messages)) {
            $newmessages = [
                [
                    'role' => 'system',
                    'content' => $promt
                ],
                [
                    'role' => 'user',
                    'content' => $msg
                ]
            ];
        } else {
            $newmessages = $messages;
            $newmessages[] = [
                'role' => 'user',
                'content' => 'reponde teniendo en cuenta tus instrucciones' . $msg
            ];
        }

        cache([$from => $newmessages], 120);

        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $newmessages,
            'temperature' => 0.1
        ];

        $payload = json_encode($data);
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openaiApiKey
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        $resultdecode = json_decode($result);
        $text1 = $resultdecode->choices[0]->message->content;
        $newmessages[] = [
            'role' => 'assistant',
            'content' => $text1
        ];
        cache([$from => $newmessages], 120);

        return $text1;
    }

    public function webhook(Request $request)
    {
        // Parse the request body from the POST
        $body = $request->all();
        $promt = env('MAINPROMT');
        //$promt_productos_obj=new ProductController;
        //$promt_productos=$promt_productos_obj->index();

        if (isset($body['object'])) {
            if (isset($body['entry']) && isset($body['entry'][0]['changes']) && isset($body['entry'][0]['changes'][0]['value']['messages']) && isset($body['entry'][0]['changes'][0]['value']['messages'][0])) {
                $cuerpo = json_encode($body);
                $this->enviarmsm("121497920919503", "573157683957", $cuerpo); //envia mensaje de whatsapp
                $phone_number_id = $body['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];
                $from = $body['entry'][0]['changes'][0]['value']['messages'][0]['from']; // Extrae numero

                if (isset($body['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])) {
                    $msg_body = $body['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']; // Extrae mensaje
                    $bandera = Whatsapp::where('Phone', $from)->get();
                    $emoji1 = "\u{1F44C}";
                    $emoji = "\u{1F609}";
                    if (count($bandera) == 1) {
                        $compra = 0;
                        $producto = '';
                        if (cache($from . 'compra') == 'true') {
                            $compra = 1;
                            if (preg_match("/^[sS]{1}[Ii]{1}$/", $msg_body)) {
                                $confirmation = new BoxController;
                                $profession = $bandera[0]->Profession;
                                $status_confirmation = $confirmation->storeforwhatsapp($from, $profession, cache($from . 'product'), cache($from . 'price'));

                                if ($status_confirmation == true) {
                                    $this->enviarmsm($phone_number_id, $from, $emoji . ' Tu solicitud de ' . cache($from . 'product') . 'se realizó exitosamente en un momento, nos comunicaremos contigo ' . $emoji1); //envia mensaje de whatsapp
                                    cache([$from . 'compra' => 'false'], 120);
                                } else {
                                    $this->enviarmsm($phone_number_id, $from, 'Ha ocurrido un error intenta nuevamente en unos segundos'); //envia mensaje de whatsapp
                                    cache([$from . 'compra' => 'false'], 120);
                                }
                            } else {
                                $this->enviarmsm($phone_number_id, $from, 'No se realizó tu solicitud, gracias por tenernos en cuenta'); //envia mensaje de whatsapp
                                cache([$from . 'compra' => 'false'], 120);
                            }
                        }
                        if (preg_match("/^[tT]{1}[aA]{1}[Xx]{1}[Ii]{1}$/", $msg_body) && cache($from . 't') != 'true' && cache($from . 'compra') != 'true') {
                            $compra = 1;
                            $producto = 'taxi ';
                            $precio = env('VALOR_MOTO');

                            $status1 = cache($from . 'product', $producto);
                            cache([$from . 'product' => $producto], 120);

                            $status2 = cache($from . 'price', $precio);
                            cache([$from . 'price' => $precio], 120);

                            $status3 = cache($from . 'compra', 'true');
                            cache([$from . 'compra' => 'true'], 120);

                            $this->enviarmsm($phone_number_id, $from, 'El costo del taxi es de ' . cache($from . 'price') . ' responde si para confirmar el servicio' . "\u{1F697}"); //envia mensaje de whatsapp


                        }

                        if (cache($from . 't') == 'true') {
                            $compra = 1;
                            $theproduct = new ProductController;
                            $productend = $theproduct->getbyid($msg_body);
                            if ($productend != 'false') {
                                $confirmation = new BoxController;
                                $productofinal = $productend[0]->Name;
                                $preciofinal = $productend[0]->Price;
                                $profession = $bandera[0]->Profession;
                                $status_confirmation = $confirmation->storeforwhatsapp($from, $profession, $productofinal, $preciofinal);
                                $this->enviarmsm($phone_number_id, $from, $emoji . ' Se confirma la compra de ' . $productofinal . ' en un momento, nos comunicaremos contigo ' . $emoji1); //envia mensaje de whatsapp
                                cache([$from . 't' => 'false'], 120);
                            } else {
                                $this->enviarmsm($phone_number_id, $from, 'En dos minutos podrás iniciar una nueva conversación'); //envia mensaje de whatsapp  
                            }
                        }

                        if (preg_match("/[Tt]{1}[Ii]{1}[Ee]{1}[Nn]{1}[Dd]{1}[Aa]{1}[uU]{1}[pP]{1}/", $msg_body) && cache($from . 't') != 'true' && cache($from . 'compra') != 'true') {
                            $compra = 1;
                            $producto = 'tienda';
                            $status = cache($from . 't', 'true');
                            cache([$from . 't' => 'true'], 120);
                            $lista_productos_obj = new ProductController;
                            $lista_productos = $lista_productos_obj->indexenumerator();
                            $this->enviarmsm($phone_number_id, $from, $lista_productos); //envia mensaje de whatsapp
                        }

                        if ($compra == 0 && cache($from . 't') != 'true' && cache($from . 'compra') != 'true') {
                            $text1 = $this->responsechat($promt, $msg_body, $from); //Obtiene respuesta de chatgpt
                            $this->enviarmsm($phone_number_id, $from, $text1); //envia mensaje de whatsapp
                        }
                    } else {
                        $this->enviarmsm($phone_number_id, $from, 'Este número no está habilitado para el servicio de Allthings para registrarlo, envía tu número, tu nombre y carrera al siguiente contacto 3182084130. '); //envia mensaje de whatsapp
                    }
                    return response('Success', 200);
                }

                if (isset($body['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'])) {
                    $id_image = $body['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'];
                    $this->enviarmsm("121497920919503", "573157683957", 'imagen id ' . $id_image); //envia mensaje de whatsapp   
                    return response('Success', 200);
                }

                if (isset($body['entry'][0]['changes'][0]['value']['messages'][0]['audio']['id'])) {
                    $id_audio = $body['entry'][0]['changes'][0]['value']['messages'][0]['audio']['id'];
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v17.0/' . $id_audio . '/',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: Bearer ' . env('WHATSAPP_TOKEN')
                        ),
                    ));

                    $responder = curl_exec($curl);
                    curl_close($curl);
                    $objetoresp = json_decode($responder);
                    $this->enviarmsm("121497920919503", "573157683957", $objetoresp->url); //envia mensaje de whatsapp   

                    $client = new Client();

                    $response = $client->get($objetoresp->url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN')
                        ],
                    ]);

                    $audioData = $response->getBody()->getContents();

                    // Crear una instancia de FFMpeg FFMpeg
                    $tempPath = tempnam(sys_get_temp_dir(), 'audio') . '.wav';

                    // Convertir el audio de MP3 a WAV
                    $success = file_put_contents($tempPath, $audioData);

                    $audiopath = Storage::disk('s3')->put('audio.mp3', file_get_contents($tempPath), 'public');
                    $localFilePath = 'audios.mp3';

                    // Descargar el archivo desde la URL
                    $fileContent = 'https://whatsappfull-bucket.s3.amazonaws.com/audio.mp3';
                    $fileName = 'audio.mp3';
                    file_put_contents($fileName, file_get_contents($fileContent));

                    // Crear el objeto CURLFile
                    $uploadFile = new CURLFile($fileName);

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.openai.com/v1/audio/transcriptions',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => array('file' => $uploadFile, 'model' => 'whisper-1'),
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: Bearer ' . env('OPENAI_API_KEY')
                        ),
                    ));

                    $respon = curl_exec($curl);

                    curl_close($curl);
                    $this->enviarmsm("121497920919503", "573157683957", $respon); //envia mensaje de whatsapp   

                    unlink($tempPath);

                    return response('Success', 200);
                }
            }
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
