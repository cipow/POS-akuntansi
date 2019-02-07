<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model {

  protected $table = 'barang__transaksi_pembelian';

  protected $guarded = ['id'];

  protected $hidden = ['transaksi_id', 'pemasok_id'];

  public $timestamps = false;

  public function transaksi() {
    return $this->belongsTo('App\Models\Transaksi\Transaksi', 'transaksi_id');
  }

  public function pemasok() {
    return $this->belongsTo('App\Models\Pemasok', 'pemasok_id');
  }

  public function hutang() {
    return $this->hasMany('App\Models\Transaksi\Pelunasan\Hutang', 'pembelian_id');
  }
}
