<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model {

  protected $table = 'users__keuangan';

  protected $guarded = ['id'];

  protected $hidden = [
    'user_id', 'transaksi_id', 'pelunasan_id', 'asset_id', 'lp_bulan_id'
  ];

  public $timestamps = false;

  public function scopeAntaraTanggal($q, array $tanggal) {
    return $q->whereBetween('tanggal', $tanggal);
  }

  public function scopeLaporanKas($q, $tanggal, $jenis) {
    return $q->bulanTahun($tanggal)->where('kategori', $jenis);
  }

  public function scopeBulanTahun($q, $tanggal) {
    return $q->whereYear('tanggal', $tanggal->year)->whereMonth('tanggal', $tanggal->month);
  }

  public function transaksi() {
    return $this->belongsTo('App\Models\Transaksi\Transaksi', 'transaksi_id');
  }

  public function pelunasan() {
    return $this->belongsTo('App\Models\Transaksi\TransaksiPelunasan', 'pelunasan_id');
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }

  public function asset() {
    return $this->belongsTo('App\Models\User\Asset', 'asset_id');
  }

  public function lpBulan() {
    return $this->belongsTo('App\Models\Laporan\Bulanan', 'lp_bulan_id');
  }
}
