<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Token extends Model {

  protected $table = 'users__token';

  protected $fillable = ['id', 'token'];

  protected $hidden = ['token', 'created_at', 'updated_at'];
}
