<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use function MongoDB\BSON\toJSON;

class CartController extends Controller
{

    function getCookieName()
    {
        return "cart_items";
    }

    /**
     * Responsible for retrieving and decoding (from json) the cart stored in the cookie
     * @return array
     */
    function getCookieCart()
    {

        $cookie_name = $this->getCookieName();
        $saved_cart_items = null;

        if (isset($_COOKIE[$cookie_name])) {
            $saved_cart_items = json_decode($_COOKIE[$cookie_name], true);
        }
        if ($saved_cart_items == null) {
            $saved_cart_items = array();
        }

        return $saved_cart_items;
    }

    /**
     * Returning all cart items as an array
     */
    function showCart()
    {
        $cart = $this->getCookieCart();
        $array_keys = array_keys($cart);

        $prods = Product::whereIn('id', $array_keys)->orderBy('id', 'desc')->get();
        $displayProds = array();
        foreach ($prods as $prod) {
            array_push($displayProds, array(
                'name' => $prod->name,
                'price' => doubleval($prod->price),
                'qty' => $this->getQtyFromCart($prod->id)
            ));
        }

        return response($displayProds, 200);

    }

    /**
     * Removing the cart cookie
     */
    function destroy()
    {
        setcookie($this->getCookieName(), "", time() - (86400 * 30), '/');
        return response("Cart deleted successfully", 204);
    }

    /**
     * @param $item_id
     * @return int - quantity of item in cart
     */
    function getQtyFromCart($item_id)
    {
        $cart = $this->getCookieCart();
        if (!isset($cart[$item_id])) {
            $cart[$item_id] = 0;
        }
        return $cart[$item_id];
    }
    /**/

    /**
     * Responsible for storing the cart in cookies and serializing it.
     * @param $cart - cart that should be serialized
     * @return bool
     */
    function setCookieCart($cart)
    {
        $cookie_name = $this->getCookieName();
        $cart_json = json_encode($cart);
        return setcookie($cookie_name, $cart_json, time() + (86400 * 30), '/'); // 86400 = 1 day
    }

    //TODO
    function addItem($item_id)
    {
        if (!Product::find($item_id)) {
            return response("Item you were trying to add doesnt exist", 404);
        }
        $cart = $this->getCookieCart();
        $cart[$item_id] = $this->getQtyFromCart($item_id) + 1;
        $this->setCookieCart($cart);

        return response(array_values($cart), 200);
    }

    //TODO
    function removeItem($item)
    {

    }

    //TODO
    function updateItem($item, $quantity)
    {

    }

    /**
     * Returns the total price of the cart with the given currency.
     * @param string $currency - Wanted currency
     * @return Response with the total price of the cart
     */
    function getTotalPrice($currency = 'usd')
    {
        $valid_cur = array('usd', 'eur');
        $currency = strtolower(($currency));

        if (!in_array($currency, $valid_cur)) {
            return response('Invalid currency selected for displaying total payment', 401);
        }

        $cart = $this->getCookieCart();
        $array_keys = array_keys($cart);

        $cur_api_response = file_get_contents('https://api.exchangeratesapi.io/latest?base=USD');
        $eur_rate = 0.92;

        // Checks if getting the currency rate finished successfully.
        if (!$cur_api_response) {
            $usd_rates_table = json_decode($cur_api_response, true);
            $eur_rate = (float)$usd_rates_table['rates']['EUR'];
        }

        $products = Product::select('price')->whereIn('id', $array_keys)->get();
        $totalPrice = 0;

        // Calculating the total
        foreach ($products as $product) {
            $qnt = $this->getQtyFromCart($product->id);
            $totalPrice += (((double)$product->price) * $qnt);
        }

        // We assume the price on the database is represented in USD
        if ($currency === 'eur') {
            $totalPrice *= $eur_rate;
        }

        return response("Total price of cart: . $totalPrice .", 200);
    }

    /**
     * Sort items by product name, price or quantity
     */
    //TODO: save to cookie after changing the array, check if 'quantity' works
    function sortItems($type)
    {
        $valid_types = array('name', 'price', 'quantity');
        $type = strtolower(($type));

        $cart = $this->getCookieCart();
        $array_keys = array_keys($cart);

        if (!in_array($type, $valid_types)) {
            return response('Invalid type selected for sorting', 401);
        }

        if ($type === 'quantity') {
            sort($cart);

//        $prods = Product::whereIn('id', $array_keys)->get();
        } else {
            $cart = Product::whereIn('id', $array_keys)->orderBy($type, 'desc')->get();
        }

        $this->setCookieCart($cart);
        return response("Cart is ordered by $type", 200);
    }


}
