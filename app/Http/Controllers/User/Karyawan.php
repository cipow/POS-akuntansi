<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class Karyawan extends Controller {

  private $user;

  private $rule = [
    'nama' => 'required|string|max:100',
    'email' => 'string|email|max:100',
    'telepon' => 'required|string|max:20',
    'alamat' => 'string',
    'gaji' => 'required|integer'
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listKaryawan() {
    return $this->response->data($this->user->karyawan()->get());
  }

  public function tambahKaryawan(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $karyawan = $this->user->karyawan()->create($req->all());
      return $this->response->data($karyawan);
    } catch (Exception $e) {
      return $this->response->serverError();
    }
  }

  public function detailKaryawan($id) {
    try {
      $karyawan = $this->user->karyawan()->findOrFail($id);
      return $this->response->data($karyawan);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Karyawan tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

  public function editKaryawan(Request $req, $id) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $karyawan = $this->user->karyawan()->findOrFail($id);
      $karyawan->update($req->all());
      return $this->response->data($karyawan);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Karyawan tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

}
