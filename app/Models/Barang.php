<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model {

  protected $table = 'barang';

  protected $fillable = ['nama', 'stok', 'stok_minimal', 'harga_rata', 'tanggal'];

  public $timestamps = false;
}
