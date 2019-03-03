<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model {

  protected $table = 'users__pelanggan';

  protected $fillable = ['nama', 'email', 'telepon', 'alamat'];

  protected $hidden = [
    'user_id'
  ];

  protected $appends = [
    'piutang'
  ];

  public function getPiutangAttribute() {
    return (int) $this->transaksi()->where('ph_utang', '>', 0)->sum('ph_utang');
  }

  public function transaksi() {
    return $this->hasMany('App\Models\Transaksi\Transaksi', 'pelanggan_id');
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }
}
