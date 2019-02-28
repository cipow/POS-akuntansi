<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model {

  protected $table = 'barang';

  protected $fillable = ['kode', 'nama', 'stok', 'stok_minimal', 'harga_rata', 'tanggal'];

  protected $hidden = [
    'user_id'
  ];

  public $timestamps = false;

  public function barangTransaksi() {
    return $this->hasMany('App\Models\Transaksi\BarangTransaksi', 'barang_id')->orderBy('id', 'desc');
  }

  public function user() {
    return $this->belongsTo('App\Models\User\User', 'user_id');
  }
}
