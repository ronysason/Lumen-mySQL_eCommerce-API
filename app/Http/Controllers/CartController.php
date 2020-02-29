<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

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

        $displayProds = array();

        foreach ($array_keys as $x) {
            $prod = Product::find($x);
            array_push($displayProds, array(
                'id' => $prod->id,
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


    /**
     * Adding an item to cart. If exist, add +1 to quantity
     * @param $item_id
     * @return Response - cart items after adding
     */
    function addItem($item_id)
    {
        // Checking if items exists in DB
        if (!Product::find($item_id)) {
            return response("Item you we're trying to add doesn't exist", 404);
        }
        $cart = $this->getCookieCart();
        $cart[$item_id] = $this->getQtyFromCart($item_id) + 1;
        $this->setCookieCart($cart);

        return response(array_values($cart), 200);
    }

    /**
     * @param $item_id - item to be removed
     * @return Response - cart after deleting
     */
    function removeItem($item_id)
    {
        $cart = $this->getCookieCart();

        if (!array_key_exists($item_id, $cart)) {
            response("Item doesn't exist in cart", 404);
        }

        unset($cart[$item_id]);
        $this->setCookieCart($cart);

        return response(array_values($cart), 200);
    }

    /**
     * @param $item_id
     * @param $quantity
     * @return Response - cart after updating
     */
    function updateItem($item_id, $quantity)
    {
        $cart = $this->getCookieCart();
        if (!array_key_exists($item_id, $cart)) {
            response("Item doesn't exist in cart", 404);
        }

        $cart[$item_id] = (int)$quantity;
        $this->setCookieCart($cart);

        return response(array_values($cart), 200);
    }

    /**
     * Returns the total price of the cart with the given currency.
     * @param string $currency - Wanted currency
     * @return Response with the total price of the cart
     */
    function getTotalPrice($currency)
    {
        $valid_cur = array('USD', 'EUR');
        $currency = strtoupper($currency);

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

        $products = Product::select('id', 'price')->whereIn('id', $array_keys)->get();
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

        return response("Total price of cart: $totalPrice $currency", 200);
    }


    /**
     * Sort items by product name, price or quantity
     */
    function sortItems($type)
    {
        $valid_types = array('name', 'price', 'quantity');
        $type = strtolower(($type));


        if (!in_array($type, $valid_types)) {
            return response('Invalid type selected for sorting. You choose between: \'name\', \'price\', \'quantity\'.', 401);
        }

        $cart = $this->getCookieCart();
        $array_keys = array_keys($cart);

        $ordered_prods = null;
        $ordered_cart = array();

        if ($type === 'quantity') {
            sort($cart);
        } else {
            $ordered_prods = Product::whereIn('id', $array_keys)->orderBy($type, 'asc')->get();

            foreach ($ordered_prods as $prod) {
                $prod_id = $prod->id;
                $ordered_cart[$prod_id] = (int)$this->getQtyFromCart($prod_id);
            }

            $cart = $ordered_cart;
        }

        $this->getCookieCart($cart);
        return response("Cart ordered by $type", 200);
    }

    /**
     * Responsible for storing the cart in cookies and encoding it.
     * @param $cart - cart array
     * @return bool
     */
    function setCookieCart($cart)
    {

        $cookie_name = $this->getCookieName();
        $cart_json = json_encode($cart);

        return setcookie($cookie_name, $cart_json, time() + (86400 * 30), '/'); // 86400 = 1 day
    }

}
