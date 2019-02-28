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

$router->post('/login', 'User\Sign@in');
$router->post('/register', 'User\Sign@up');

$router->group(['middleware' => 'jwt'], function($router) {
  $router->group(['prefix' => 'profil'], function($router) {
    $router->get('/', 'User\Profil@index');
    $router->post('/modal', 'User\Profil@modalAwal');
    $router->get('/keuangan', 'User\Profil@riwayatKeuangan');
    $router->get('/keuangan/{id}', 'User\Profil@detailKeuangan');
  });

  $router->group(['prefix' => '/barang'], function($router) {
    $router->get('/', 'User\Barang@listBarang');
    $router->post('/', 'User\Barang@tambahBarang');
    $router->get('/{id}', 'User\Barang@detailBarang');
    $router->put('/{id}', 'User\Barang@editBarang');
    // $router->delete('/{id}', 'Barang@hapusBarang');

  });

  $router->group(['prefix' => '/pemasok'], function($router) {
    $router->get('/', 'User\Pemasok@listPemasok');
    $router->post('/', 'User\Pemasok@tambahPemasok');
    $router->get('/{id}', 'User\Pemasok@detailPemasok');
    $router->put('/{id}', 'User\Pemasok@editPemasok');
  });

  $router->group(['prefix' => '/pelanggan'], function($router) {
    $router->get('/', 'User\Pelanggan@listPelanggan');
    $router->post('/', 'User\Pelanggan@tambahPelanggan');
    $router->get('/{id}', 'User\Pelanggan@detailPelanggan');
    $router->put('/{id}', 'User\Pelanggan@editPelanggan');
  });

  $router->group(['middleware' => 'modal'], function($router) {
    $router->group(['prefix' => '/transaksi'], function($router) {
      $router->get('/', 'Transaksi\Transaksi@daftarTransaksi');
      $router->post('/beli', 'Transaksi\Transaksi@beli');
      $router->post('/jual', 'Transaksi\Transaksi@jual');
      $router->get('/{id}', 'Transaksi\Transaksi@dataTransaksi');
      $router->post('/{id}/pelunasan', 'Transaksi\Transaksi@pelunasan');
    });

    $router->group(['prefix' => '/laporan'], function($router) {
      $router->get('/', 'Transaksi\Laporan@dataLaporan');
      $router->get('/bulanan', 'Transaksi\Laporan@laporanBulanan');
      $router->post('/bulanan', 'Transaksi\Laporan@simpanLaporanBulanan');
    });

  });

});
