<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;

class TransaksiPelunasan extends Model {

  protected $table = 'transaksi__pelunasan';

  protected $guarded = ['id'];

  protected $hidden = ['transaksi_id'];

  public $timestamps = false;

  public function transaksi() {
    return $this->belongsTo('App\Models\Transaksi\Transaksi', 'transaksi_id');
  }

  public function keuangan() {
    return $this->hasOne('App\Models\User\Keuangan', 'pelunasan_id');
  }
}
