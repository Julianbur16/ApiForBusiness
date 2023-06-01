<?php

namespace App\Http\Controllers;

use App\Models\box;
use Illuminate\Http\Request;

class BoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $boxes=box::all();
        return response()->json($boxes);
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
        $table->string('Celular');
            $table->string('Carrera')->nullable();
            $table->string('Producto');
        */
        /*
        $boxes=new box();
        $boxes->Celular=$request->Celular;
        $boxes->Carrera=$request->Carrera;
        $boxes->Producto=$request->Producto;
        $boxes->save();
        return response()->json($boxes);
        */
    }

    public function storeforwhatsapp(string $phone,string $chose,string $product,string $price){
        $boxes=new box();
        $boxes->Celular=$phone;
        $boxes->Carrera=$chose;
        $boxes->Producto=$product;
        $boxes->Precio=$price;
        $boxes->save();
        return true;
    }

    /**
     * Display the specified resource.
     */
    public function show(box $box)
    {
        $boxes=box :: find($box);
        if($boxes != null){
            return response()->json($boxes);
        }else{
            $data=["message"=>"cliente no existe"];
            return response()->json($data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(box $box)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, box $box)
    {
        /*
        $table->string('Celular');
            $table->string('Carrera')->nullable();
            $table->string('Producto');
        */
        /*
        $box->Celular=$request->Celular;
        $box->Carrera=$request->Carrera;
        $box->Producto=$request->Producto;
        $box->save();
        $data=[
            "message"=>"client update successfully",
            "client"=>"$box"
        ];

        return response()->json($data);
        */
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(box $box)
    {
        $boxes=box ::find($box->id);
        $boxes->delete();
        $data=["message"=>"client delete successfully"];
        return response()->json($data);
    }
}
