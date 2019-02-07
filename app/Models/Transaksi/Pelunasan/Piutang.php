<?php

namespace App\Models\Transaksi\Pelunasan;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model {

  protected $table = 'barang__pelunasan_piutang';

  protected $guarded = ['id'];

  protected $hidden = ['penjualan_id'];

  public $timestamps = false;

  public function piutang() {
    return $this->belongsTo('App\Models\Transaksi\Penjualan', 'penjualan_id');
  }
}
