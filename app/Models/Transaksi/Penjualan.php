<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model {

  protected $table = 'barang__transaksi_penjualan';

  protected $guarded = ['id'];

  public $timestamps = false;

  public function transaksi() {
    return $this->belongsTo('App\Models\Transaksi\Transaksi', 'transaksi_id');
  }

  public function pelanggan() {
    return $this->belongsTo('App\Models\Pelanggan', 'pelanggan_id');
  }

  public function piutang() {
    return $this->hasMany('App\Models\Transaksi\Pelunasan\Piutang', 'penjualan_id');
  }
}
