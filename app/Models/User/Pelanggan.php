<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model {

  protected $table = 'users__pelanggan';

  protected $fillable = ['nama', 'email', 'telepon', 'alamat'];

  protected $hidden = [
    'user_id'
  ];

  public function transaksi() {
    return $this->hasMany('App\Models\Transaksi\Transaksi', 'pelanggan_id');
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }
}
