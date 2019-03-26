<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model {

  protected $table = 'users__asset';

  protected $guarded = ['id'];

  protected $hidden = [
    'user_id'
  ];

  public $timestamps = false;

  public function scopeLaporanKas($q, $tanggal, $jenis) {
    return $q->bulanTahun($tanggal)->where('kategori', $jenis);
  }

  public function scopeBulanTahun($q, $tanggal) {
    return $q->whereYear('tanggal', $tanggal->year)->whereMonth('tanggal', $tanggal->month);
  }

  public function scopeBelumKadaluarsa($q) {
    return $q->where('nilai_sekarang', '<', 'harga_beli');
  }

  public function scopeKategori($q, $kategori) {
    return $q->where('kategori', $kategori);
  }

  public function keuangan() {
    return $this->hasOne('App\Models\User\Keuangan', 'asset_id');
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }
}
