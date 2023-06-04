<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products=product::all();
        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function indexenumerator(){
        $products=product::all();
        $productsenumerate=$products->map(function ($products,$indice) {
            return $products->id . ". " . $products->Name . "  ---------  ". $products->Price;
        })->implode(PHP_EOL);
        return "Indica el código del producto de tu preferencia\n\n"."Productos:\n" .$productsenumerate;

    }

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
       $table->string('Name');
            $table->string('Description');
            $table->string('Price');
            $table->string('Kind');
            $table->string('Availability');
       */
    
        $products=new product();
        $products->Name=$request->Name;
        $products->Description=$request->Description;
        $products->Price=$request->Price;
        $products->Kind=$request->Kind;
        $products->Availability=$request->Availability;
        $products->save();
        return response()->json($products);

    }

    /**
     * Display the specified resource.
     */
    public function show(product $product)
    {
        $products=product :: find($product);
        if($products != null){
            return response()->json($products);
        }else{
            $data=["message"=>"cliente no existe"];
            return response()->json($data);
        }
    }

    public function getbyid(string $id){
        $products=product::where('id',$id)->get();
        if(count($products)==1){
            return $products;
        }else{
            return 'false';
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, product $product)
    {
       /*
       $table->string('Name');
            $table->string('Description');
            $table->string('Price');
            $table->string('Kind');
            $table->string('Availability');
       */
        $product->Name=$request->Name;
        $product->Description=$request->Description;
        $product->Price=$request->Price;
        $product->Kind=$request->Kind;
        $product->Availability=$request->Availability;
        $product->save();
        $data=[
            "message"=>"client update successfully",
            "client"=>"$product"
        ];

        return response()->json($data);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product $product)
    {
        $products=product ::find($product->id);
        $products->delete();
        $data=["message"=>"client delete successfully"];
        return response()->json($data);
    }
}
