<?php

namespace App\Http\Controllers;

use App\Catalog;
use App\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CatalogController extends Controller
{

    public function showCatalogProducts($id)
    {
        $catalog = Catalog::find($id);
        $productInCatalog = array();

        foreach ($catalog->products as $product) {
            array_push($productInCatalog, $product);
        }

        return response()->json($productInCatalog, 201);
    }

    public function createNewCatalog(Request $request)
    {
        $catalog = Catalog::create($request->all());
        return response()->json($catalog, 201);
    }

    /**
     * @param Request $request - PATCH query parameters
     * @return string
     */
    public function attach(Request $request)
    {
        $catalog_id = $request->input('catalog');
        $product_id = $request->input('product');

        $catalog = Catalog::find($catalog_id);
        $product = Product::find($product_id);

        $catalog->products()->attach($product);
        return 'Attached successfully';
    }


    /**
     * @param Request $request - PATCH query parameters
     * @return string
     */
    public function detach(Request $request)
    {
        $catalog_id = $request->input('catalog');
        $product_id = $request->input('product');

        $catalog = Catalog::find($catalog_id);
        $product = Product::find($product_id);

        $catalog->products()->detach($product);
        return 'Detached successfully';
    }
}
