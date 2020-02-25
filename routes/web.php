<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $router->get('/cookie/set','CookieController@setCookie');
// $router->get('/cookie/get','CookieController@getCookie');

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {

    $router->get('products/filter', ['uses' => 'ProductController@filterProductsByName']);

    $router->get('products', ['uses' => 'ProductController@showAllProducts']);

    $router->get('products/{id}', ['uses' => 'ProductController@showProductById']);

    $router->post('product', ['uses' => 'ProductController@addNewProduct']);

    $router->delete('products/{id}', ['uses' => 'ProductController@deleteProduct']);

    $router->post('catalog', ['uses' => 'CatalogController@createNewCatalog']);

    $router->get('catalog/{id}', ['uses' => 'CatalogController@showCatalogProducts']);

    $router->patch('catalog/attach', ['uses' => 'CatalogController@attach']);

    $router->patch('catalog/detach', ['uses' => 'CatalogController@detach']);
});

$router->group(['prefix' => 'cart'], function () use ($router) {

    /**
     * Routes for cookies check
     */

    $router->get('show', ['uses' => 'CartController@showCart']);

    $router->delete('delete', ['uses' => 'CartController@destroy']);

    $router->post('add/{item}', ['uses' => 'CartController@addItem']);

    $router->post('remove/{item}/{quantity?}', ['uses' => 'CartController@removeItem']);

    $router->patch('item/{item_id}/quantity/{quantity}', ['uses' => 'CartController@updateItem']);

    $router->get('totalPrice/{currency?}', ['uses' => 'CartController@getTotalPrice']);

    $router->patch('sort/{type}', ['uses' => 'CartController@sortItems']);

});
