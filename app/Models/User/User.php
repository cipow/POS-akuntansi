<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class User extends Model {

  protected $table = 'users';

  protected $fillable = ['nama', 'email', 'password', 'kas', 'modal'];

  protected $hidden = ['password'];

  public function barang() {
    return $this->hasMany('App\Models\User\Barang', 'user_id');
  }

  public function pemasok() {
    return $this->hasMany('App\Models\User\Pemasok', 'user_id');
  }

  public function pelanggan() {
    return $this->hasMany('App\Models\User\Pelanggan', 'user_id');
  }

  public function lpBulan() {
    return $this->hasMany('App\Models\Laporan\Bulanan', 'user_id');
  }

  public function keuangan() {
    return $this->hasMany('App\Models\User\Keuangan', 'user_id');
  }

  public function asset() {
    return $this->hasMany('App\Models\User\Asset', 'user_id');
  }

}
