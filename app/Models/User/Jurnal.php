<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model {

  protected $table = 'users__log_jurnal';

  protected $guarded = ['id'];

  protected $hidden = [
    'user_id'
  ];

  public $timestamps = false;

  public function scopeBulanTahun($q, $tanggal) {
    return $q->whereYear('tanggal', $tanggal->year)->whereMonth('tanggal', $tanggal->month);
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }
}
