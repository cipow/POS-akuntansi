<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model {

  protected $table = 'users__pemasok';

  protected $fillable = [
    'nama', 'email', 'telepon', 'alamat', 'bank', 'no_rekening', 'an_rekening'
  ];

  protected $hidden = [
    'user_id'
  ];

  public function transaksi() {
    return $this->hasMany('App\Models\Transaksi\Transaksi', 'pemasok_id');
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }
}
