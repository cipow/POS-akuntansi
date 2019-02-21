<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model {

  protected $table = 'transaksi';

  protected $guarded = ['id'];

  protected $hidden = ['pelanggan_id', 'pemasok_id'];

  public $timestamps = false;

  public function scopeLaporanTransaksi($q, $tanggal, $jenis) {
    return $q->bulanTahun($tanggal)->where('jenis', $jenis);
  }

  public function scopeBulanTahun($q, $tanggal) {
    return $q->whereYear('tanggal', $tanggal->year)->whereMonth('tanggal', $tanggal->month);
  }

  public function scopeBulanTahunSebelumnya($q, $tanggal) {
    return $q->whereYear('tanggal', $tanggal->year)->whereMonth('tanggal', '<', $tanggal->month);
  }

  public function pemasok() {
    return $this->belongsTo('App\Models\Pemasok', 'pemasok_id');
  }

  public function pelanggan() {
    return $this->belongsTo('App\Models\Pelanggan', 'pelanggan_id');
  }

  public function barangTransaksi() {
    return $this->hasMany('App\Models\Transaksi\BarangTransaksi', 'transaksi_id');
  }

  public function pelunasan() {
    return $this->hasMany('App\Models\Transaksi\TransaksiPelunasan', 'transaksi_id');
  }

}
