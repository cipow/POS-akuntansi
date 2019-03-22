<?php

namespace App\Models\Laporan;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model {

  protected $table = 'laporan_kas';

  protected $guarded = ['id'];

  protected $hidden = [
    'user_id'
  ];

  public $timestamps = false;

  public function scopeBulanTahun($q, $tanggal) {
    return $q->whereYear('tanggal_laporan', $tanggal->year)->whereMonth('tanggal_laporan', $tanggal->month);
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }


}
