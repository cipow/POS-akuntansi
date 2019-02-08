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

    $router->group(['prefix' => '{id}/pembelian'], function($router) {
      $router->post('/', 'Pembelian@add');
      $router->get('/', 'Pembelian@listPembelian');
      $router->get('/{pembelian_id}', 'Pembelian@getPembelian');
      $router->post('/{pembelian_id}', 'Pembelian@pelunasan');
      $router->delete('/{pembelian_id}/{hutang_id}', 'Pembelian@hapusPelunasan');
    });

    $router->group(['prefix' => '{id}/penjualan'], function($router) {
      $router->post('/', 'Penjualan@add');
      $router->get('/', 'Penjualan@listPenjualan');
      $router->get('/{penjualan_id}', 'Penjualan@getPenjualan');
      $router->post('/{penjualan_id}', 'Penjualan@pembayaran');
      $router->delete('/{penjualan_id}/{piutang_id}', 'Penjualan@hapusPembayaran');
    });

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


});
