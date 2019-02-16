<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;

class BarangTransaksi extends Model {

  protected $table = 'barang_transaksi';

  protected $guarded = ['id'];

  protected $hidden = ['barang_id', 'transaksi_id'];

  public $timestamps = false;

  public function barang() {
    return $this->belongsTo('App\Models\Barang', 'barang_id');
  }

  public function transaksi() {
    return $this->belongsTo('App\Models\Transaksi\Transaksi', 'transaksi_id');
  }
}
