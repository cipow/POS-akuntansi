<?php

namespace App\Models\Laporan;

use Illuminate\Database\Eloquent\Model;

class Bulanan extends Model {

  protected $table = 'laporan_bulanan';

  protected $guarded = ['id'];

  public $timestamps = false;

  public function scopeBulanTahun($q, $tanggal) {
    return $q->whereYear('tanggal', $tanggal->year)->whereMonth('tanggal', $tanggal->month);
  }

}
