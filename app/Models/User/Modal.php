<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Modal extends Model {

  protected $table = 'users__perubahan_modal';

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
