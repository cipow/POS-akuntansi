<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model {

  protected $table = 'barang__pemasok';

  protected $fillable = [
    'nama', 'email', 'telepon', 'alamat', 'bank', 'no_rekening', 'an_rekening'
  ];
}
