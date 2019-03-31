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
    $router->post('/prive', 'User\Profil@prive');
    $router->get('/keuangan', 'User\Profil@riwayatKeuangan');
    $router->get('/jurnal', 'User\Profil@jurnal');
    $router->get('/keuangan/{id}', 'User\Profil@detailKeuangan');
  });

  $router->group(['prefix' => '/barang'], function($router) {
    $router->get('/', 'User\Barang@listBarang');
    $router->post('/', 'User\Barang@tambahBarang');
    $router->get('/{id}', 'User\Barang@detailBarang');
    $router->put('/{id}', 'User\Barang@editBarang');
    $router->get('/{id}/transaksi', 'User\Barang@detailBarangTransaksi');
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

  $router->group(['prefix' => '/karyawan'], function($router) {
    $router->get('/', 'User\Karyawan@listKaryawan');
    $router->post('/', 'User\Karyawan@tambahKaryawan');
    $router->get('/{id}', 'User\Karyawan@detailKaryawan');
    $router->put('/{id}', 'User\Karyawan@editKaryawan');
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
      $router->get('/modal', 'Transaksi\Laporan@laporanModals');
      $router->post('/modal', 'Transaksi\Laporan@simpanLaporanModal');
      $router->get('/kas', 'Transaksi\Laporan@laporanKas');
      $router->post('/kas', 'Transaksi\Laporan@simpanLaporanKas');
      $router->get('/neraca', 'Transaksi\Laporan@laporanNeraca');
      $router->post('/neraca', 'Transaksi\Laporan@simpanLaporanNeraca');
      $router->get('/modal/riwayat', 'Transaksi\Laporan@riwayatLaporanModal');
      $router->get('/kas/riwayat', 'Transaksi\Laporan@riwayatLaporanKas');
      $router->get('/neraca/riwayat', 'Transaksi\Laporan@riwayatLaporanNeraca');
    });

    $router->group(['prefix' => '/asset'], function($router) {
      $router->get('/', 'Transaksi\Asset@daftar');
      $router->post('/', 'Transaksi\Asset@tambah');
    });

  });

});
