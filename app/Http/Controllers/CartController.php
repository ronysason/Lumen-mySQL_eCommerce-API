<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Cart_Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{

  //TODO
  function newCart() {
    $cart = Cart::create([
      'id' => md5(uniqid(rand(), true)
    ])

    return response('C Successfully', 201);
  }

  //TODO
  function showCart() {

  }

  //TODO
  function destroy() {

  }

  //TODO
  function addItem($item) {

  }

  //TODO
  function removeItem($item) {

  }

  //TODO
  function updateItem($item, $count) {

  }

  //TODO
  function getTotalPrice($currency){

  }

  //TODO
  /*
  Sort items by product name, price or quantity
  */
  function sortItems($type) {

  }


}
