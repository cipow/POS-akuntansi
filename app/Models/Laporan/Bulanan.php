<?php

namespace App\Models\Laporan;

use Illuminate\Database\Eloquent\Model;

class Bulanan extends Model {

  protected $table = 'laporan_bulanan';

  protected $guarded = ['id'];

  public $timestamps = false;

  public function scopeBulanTahun($q, $tanggal) {
    return $q->whereYear('tanggal_laporan', $tanggal->year)->whereMonth('tanggal_laporan', $tanggal->month);
  }

}
