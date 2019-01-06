<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model {

  protected $table = 'barang__pelanggan';

  protected $fillable = ['nama', 'email', 'telepon', 'alamat'];
}
