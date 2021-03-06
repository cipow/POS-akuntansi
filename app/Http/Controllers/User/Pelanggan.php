<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class Pelanggan extends Controller {

  private $user;

  private $rule = [
    'nama' => 'required|string|max:100',
    'email' => 'string|email|max:100',
    'telepon' => 'required|string|max:20',
    'alamat' => 'string',
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listPelanggan() {
    return $this->response->data($this->user->pelanggan()->get());
  }

  public function tambahPelanggan(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $pelanggan = $this->user->pelanggan()->create($req->all());
      return $this->response->data($pelanggan);
    } catch (Exception $e) {
      return $this->response->serverError();
    }
  }

  public function detailPelanggan($id) {
    try {
      $pelanggan = $this->user->pelanggan()->findOrFail($id);
      return $this->response->data($pelanggan);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Pelanggan tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

  public function editPelanggan(Request $req, $id) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $pelanggan = $this->user->pelanggan()->findOrFail($id);
      $pelanggan->update($req->all());
      return $this->response->data($pelanggan);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Pelanggan tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

}
