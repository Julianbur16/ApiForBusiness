<?php

namespace App\Http\Controllers;

use App\Models\Whatsapp;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller 
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $whatsapps=whatsapp::all();
        return response()->json($whatsapps);
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
        /*
        $table->string('Phone')->unique();
            $table->string('Profession')->nullable();
            $table->string('Name')->nullable();
            $table->string('City')->nullable();
        */
        $whatsapps=new Whatsapp();
        $whatsapps->Phone=$request->Phone;
        $whatsapps->Profession=$request->Profession;
        $whatsapps->Name=$request->Name;
        $whatsapps->City=$request->City;
        $whatsapps->save();
        return response()->json($whatsapps);
    }

    /**
     * Display the specified resource.
     */
    public function show(Whatsapp $whatsapp)
    {
        $whatsapps=whatsapp :: find($whatsapp);
        if($whatsapps != null){
            return response()->json($whatsapps);
        }else{
            $data=["message"=>"cliente no existe"];
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
        $whatsapp->Phone=$request->Phone;
        $whatsapp->Profession=$request->Profession;
        $whatsapp->Name=$request->Name;
        $whatsapp->City=$request->City;
        $whatsapp->save();
        $data=[
            "message"=>"client update successfully",
            "client"=>"$whatsapp"
        ];

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Whatsapp $whatsapp)
    {
        $whatsapps=whatsapp ::find($whatsapp->id);
        $whatsapps->delete();
        $data=["message"=>"client delete successfully"];
        return response()->json($data);
    }

    public function enviarmsm(string $phone_number_id, string $from, string $text){
        $client = new Client();
        $response = $client->post('https://graph.facebook.com/v12.0/' . $phone_number_id . '/messages?access_token=' . env('WHATSAPP_TOKEN'), [
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'text' => ['body' => $text],
            ],
        ]);
    }

    public function responsechat($promt, $msg, $from){
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
                'content' => 'reponde teniendo en cuenta tus instrucciones'.$msg
            ];
        }
        
        cache([$from => $newmessages],120);
        
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
            'Authorization: Bearer '.$openaiApiKey
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
                $phone_number_id = $body['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];
                $from = $body['entry'][0]['changes'][0]['value']['messages'][0]['from']; // Extrae numero
                $msg_body = $body['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']; // Extrae mensaje
                $bandera=Whatsapp::where('Phone',$from)->get();
                

                if(count($bandera)==1){
                    $compra=0;
                    $producto='';
                    if (preg_match("/^[mM]{1}[oO]{1}[tT]{1}[Oo]{1}$/", $msg_body)) {
                        $compra=1;
                        $producto='Moto taxi';
                        $confirmation=new BoxController;
                        $status_confirmation=$confirmation->storeforwhatsapp($from, 'mecatronica',$producto);
                        if($status_confirmation==true){
                            $this->enviarmsm($phone_number_id,$from,'Tu pedido de '.$producto. 'se realizo exitosamente en un momento nos comunicaremos contigo');//envia mensaje de whatsapp
                        }else{
                            $this->enviarmsm($phone_number_id,$from,'Ha ocurrido un error intenta nuevamente en unos segundos');//envia mensaje de whatsapp
                        }
                        

                    } 

                    if (preg_match("/^[Dd]{1}[Oo]{1}[Mm]{1}[Ii]{1}[Cc]{1}[Ii]{1}[Ll]{1}[Ii]{1}([Oo]{1}|[Oo]{1}[Ss]{1})$/", $msg_body)) {
                        $compra=1;
                        $producto='domicilio';
                        $this->enviarmsm($phone_number_id,$from,'Tu pedido de '.$producto. 'se realizo exitosamente en un momento nos comunicaremos contigo');//envia mensaje de whatsapp
                    }

                    if (preg_match("/^[Aa]{1}[Ss]{1}[Ee]{1}[Ss]{1}[Oo]{1}[Rr]{1}[Ii]{1}([Aa]{1}|[Aa]{1}[Ss]{1})$/", $msg_body)) {
                        $compra=1;
                        $producto='asesoria';
                        $this->enviarmsm($phone_number_id,$from,'Tu servicio de '.$producto. 'se registro exitosamente en un momento nos comunicaremos contigo');//envia mensaje de whatsapp
                    }

                    if(cache($from.'t')=='true'){
                        $compra=1;
                        if($msg_body=='1'){
                        $this->enviarmsm($phone_number_id,$from,'Se ha realizado exitosamente su compra');//envia mensaje de whatsapp
                        cache([$from.'t' => 'false'], 180);
                        }else{
                            $this->enviarmsm($phone_number_id,$from,'Número invalido');//envia mensaje de whatsapp  
                        }
                    }
                    
                    if (preg_match("/^[Tt]{1}[Ii]{1}[Ee]{1}[Nn]{1}[Dd]{1}[Aa]{1}$/", $msg_body)) {
                        $compra=1;
                        $producto='tienda';
                        $status = cache($from.'t', 'true');
                        cache([$from.'t' => 'true'], 180);
                        $lista_productos_obj=new ProductController;
                        $lista_productos=$lista_productos_obj->indexenumerator();
                        $this->enviarmsm($phone_number_id,$from,$lista_productos);//envia mensaje de whatsapp
                    }
                   
                    if($compra==0){
                    $text1=$this->responsechat($promt,$msg_body,$from);//Obtiene respuesta de chatgpt
                    $this->enviarmsm($phone_number_id,$from,$text1);//envia mensaje de whatsapp
                    }
                   
                }else{
                    $this->enviarmsm($phone_number_id,$from,'Numero no registrado');//envia mensaje de whatsapp
                }
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
