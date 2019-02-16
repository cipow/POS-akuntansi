<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model {

  protected $table = 'pelanggan';

  protected $fillable = ['nama', 'email', 'telepon', 'alamat'];

  public function transaksi() {
    return $this->hasMany('App\Models\Transaksi\Transaksi', 'pelanggan_id');
  }
}
