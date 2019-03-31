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

  public function karyawan() {
    return $this->hasMany('App\Models\User\Karyawan', 'user_id');
  }

  public function lpBulan() {
    return $this->hasMany('App\Models\Laporan\Bulanan', 'user_id');
  }

  public function lpKas() {
    return $this->hasMany('App\Models\Laporan\Kas', 'user_id');
  }

  public function lpNeraca() {
    return $this->hasMany('App\Models\Laporan\Neraca', 'user_id');
  }

  public function keuangan() {
    return $this->hasMany('App\Models\User\Keuangan', 'user_id');
  }

  public function asset() {
    return $this->hasMany('App\Models\User\Asset', 'user_id');
  }

  public function jurnal() {
    return $this->hasMany('App\Models\User\Jurnal', 'user_id');
  }

  public function perubahanModal() {
    return $this->hasMany('App\Models\User\Modal', 'user_id');
  }

}
