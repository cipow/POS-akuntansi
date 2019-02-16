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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/login', 'Sign@in');
$router->post('/register', 'Sign@up');

$router->group(['middleware' => 'jwt'], function($router) {
  $router->group(['prefix' => '/barang'], function($router) {
    $router->get('/', 'Barang@listBarang');
    $router->post('/', 'Barang@tambahBarang');
    $router->get('/{id}', 'Barang@detailBarang');
    $router->put('/{id}', 'Barang@editBarang');
    // $router->delete('/{id}', 'Barang@hapusBarang');

  });

  $router->group(['prefix' => '/pemasok'], function($router) {
    $router->get('/', 'Pemasok@listPemasok');
    $router->post('/', 'Pemasok@tambahPemasok');
    $router->get('/{id}', 'Pemasok@detailPemasok');
    $router->put('/{id}', 'Pemasok@editPemasok');
  });

  $router->group(['prefix' => '/pelanggan'], function($router) {
    $router->get('/', 'Pelanggan@listPelanggan');
    $router->post('/', 'Pelanggan@tambahPelanggan');
    $router->get('/{id}', 'Pelanggan@detailPelanggan');
    $router->put('/{id}', 'Pelanggan@editPelanggan');
  });

  $router->group(['prefix' => '/transaksi'], function($router) {
    $router->get('/', 'Transaksi\Transaksi@daftarTransaksi');
    $router->post('/beli', 'Transaksi\Transaksi@beli');
    $router->post('/jual', 'Transaksi\Transaksi@jual');
    $router->get('/{id}', 'Transaksi\Transaksi@dataTransaksi');
    $router->post('/{id}/pelunasan', 'Transaksi\Transaksi@pelunasan');
  });

});
