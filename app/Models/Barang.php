<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model {

  protected $table = 'barang';

  protected $fillable = ['kode', 'nama', 'stok', 'stok_minimal', 'harga_rata', 'tanggal'];

  public $timestamps = false;

  public function barangTransaksi() {
    return $this->hasMany('App\Models\Transaksi\BarangTransaksi', 'barang_id');
  }
}
