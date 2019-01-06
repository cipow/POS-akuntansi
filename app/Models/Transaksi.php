<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model {

  protected $table = 'barang__transaski';

  protected $fillable = [
    'nofaktur_pembelian', 'nofaktur_penjualan', 'tanggal',
    'masuk_kg', 'harga_beli', 'total_pembelian',
    'keluar_kg', 'harga_jual', 'total_penjualan',
    'saldo_kg', 'harga_rata'
  ];
}
