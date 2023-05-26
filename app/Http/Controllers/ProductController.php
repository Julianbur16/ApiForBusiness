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
        $productsenumerate=$products->pluck('Name','Price')->map(function ($products,$indice) {
            return ($indice + 1) . ". " . $products->Name . "---------". $products->Price;
        })->implode(PHP_EOL);
        return "Productos:\n" .$productsenumerate;

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(product $product)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product $product)
    {
        //
    }
}
