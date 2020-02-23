<?php

namespace App\Http\Controllers;

use App\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Add a new product to the database
     */
    public function addNewProduct(Request $request)
    {
        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    /**
     * Delete a product from the database given it's id
     */
    public function deleteProduct($id)
    {
        Product::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }

    /**
     * Show all products in the database
     */
    public function showAllProducts()
    {
        return response()->json(Product::all());
    }

    /**
     * Show a product given it's id
     */
    public function showProductById($id)
    {
        return response()->json(Product::find($id));
    }

    //TODO: Fix bugs & Check on Postman
    /**
     * Show all products in the database containing the given string in their name
     */
    public function filterProductsByName(Request $request, Product $product)
    {
        if ($request->has('filter')) {
            return $product->where('name', 'like', '%' . $filter . '%')->get();
        } else {
            return null;
        }
    }
}
