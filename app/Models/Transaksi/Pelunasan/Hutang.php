<?php

namespace App\Models\Transaksi\Pelunasan;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model {

  protected $table = 'barang__pelunasan_hutang';

  protected $guarded = ['id'];

  protected $hidden = ['pembelian_id'];

  public $timestamps = false;

  public function pembelian() {
    return $this->belongsTo('App\Models\Transaksi\Pembelian', 'pembelian_id');
  }
}
