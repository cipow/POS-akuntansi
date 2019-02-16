<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model {

  protected $table = 'pemasok';

  protected $fillable = [
    'nama', 'email', 'telepon', 'alamat', 'bank', 'no_rekening', 'an_rekening'
  ];

  public function transaksi() {
    return $this->hasMany('App\Models\Transaksi\Transaksi', 'pemasok_id');
  }
}
