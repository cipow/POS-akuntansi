<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model {

  protected $table = 'barang__transaksi';

  protected $guarded = ['id'];

  protected $hidden = ['barang_id'];

  public $timestamps = false;

  public function barang() {
    return $this->belongsTo('App\Models\Barang', 'barang_id');
  }

  public function pembelian() {
    return $this->hasOne('App\Models\Transaksi\Pembelian', 'transaksi_id');
  }

  public function penjualan() {
    return $this->hasOne('App\Models\Transaksi\Penjualan', 'transaksi_id');
  }


}
