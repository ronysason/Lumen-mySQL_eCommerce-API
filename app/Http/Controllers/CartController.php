<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use function MongoDB\BSON\toJSON;

class CartController extends Controller
{

  //TODO
  function showCart() {
    $cart = $this->getCookieCart();
    $array_keys = array_keys($cart);

    $prods = Product::whereIn('id', $array_keys)->orderBy('id', 'desc')->get();
    $displayProds = array();
      foreach ($prods as $prod) {
          array_push($displayProds,array(
              'name' => $prod->name,
              'price' => doubleval($prod->price),
              'qty' => $this->getQtyFromCart($prod->id)
          ));
      }

    return response($displayProds, 200);

  }

  function destroy() {
      setcookie($this->getCookieName(), "", time()-(86400 * 30) , '/');
  }

  function getQtyFromCart($item_id){
      $cart = $this->getCookieCart();
      if(!isset($cart[$item_id])){
          $cart[$item_id] = 0;
      }
      return $cart[$item_id];
  }

  function getCookieName(){
      return "cart_items";
  }

    /**
     * Responsible for retrieving and unserializing the cart stored in the cookie
     * @return array
     */
  function getCookieCart(){

    $cookie_name = $this->getCookieName();
    $saved_cart_items = null;

    if (isset($_COOKIE[$cookie_name])) {
      $saved_cart_items = json_decode($_COOKIE[$cookie_name], true);
    }
    if($saved_cart_items == null){
        $saved_cart_items = array();
    }

    return $saved_cart_items;
  }

    /**
     * Responsible for storing the cart in cookies and serializing it.
     * @param $cart - cart that should be serialized
     * @return bool
     */
  function setCookieCart($cart) {
      $cookie_name = $this->getCookieName();
      $cart_json = json_encode($cart);
      return setcookie($cookie_name, $cart_json, time() + (86400 * 30), '/'); // 86400 = 1 day
  }

  //TODO
  function addItem($item_id) {
      if(!Product::find($item_id)){
          return response("Item you were trying to add doesnt exist", 404);
      }
    $cart = $this->getCookieCart();
    $cart[$item_id] =  $this->getQtyFromCart($item_id) + 1;
    $this->setCookieCart($cart);

    return response(array_values($cart), 200);

  }

  //TODO
  function removeItem($item) {

  }

  //TODO
  function updateItem($item, $quantity) {

  }

  //TODO
  function getTotalPrice($currency){
      $cart = $this->getCookieCart();
      $array_keys = array_keys($cart);

      $usd_rate = json_decode(file_get_contents('https://api.exchangeratesapi.io/latest?base=USD'), true);
      $eur_rate = (float) $usd_rate['rates']['EUR'];

      $prices = Product::select('price')->whereIn('id', $array_keys)->get();
      $totalPrice = 0;

      foreach($prices as $price) {
          $totalPrice += (double) $price->price;
      }

      echo $totalPrice;
  }

  /*
  Sort items by product name, price or quantity
  */
  function sortItems($type) {
      $valid_types = array('name', 'price', 'quantity');
      $type = strtolower(($type));
      $cart = $this->getCookieCart();
      $prods = null;
      $array_keys = array_keys($cart);

      if(!in_array($type, $valid_types)){
          return response('Invalid type selected for sorting', 401);
      }

      if($type === 'quantity'){
          sort($cart);

//        $prods = Product::whereIn('id', $array_keys)->get();
      }
      else{
          $prods = Product::whereIn('id', $array_keys)->orderBy($type, 'desc')->get();
      }

      return $prods;
  }


}
