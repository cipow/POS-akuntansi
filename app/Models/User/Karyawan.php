<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model {

  protected $table = 'users__karyawan';

  protected $fillable = ['nama', 'email', 'telepon', 'alamat', 'gaji'];

  protected $hidden = [
    'user_id'
  ];

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }
}
