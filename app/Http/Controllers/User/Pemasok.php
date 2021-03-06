<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class Pemasok extends Controller {

  private $user;

  private $rule = [
    'nama' => 'required|string|max:100',
    'email' => 'string|email|max:100',
    'telepon' => 'required|string|max:20',
    'alamat' => 'string',
    'bank' => 'string|max:100',
    'no_rekening' => 'string|max:100',
    'an_rekening' => 'string|max:100',
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listPemasok() {
    return $this->response->data($this->user->pemasok()->get());
  }

  public function tambahPemasok(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $pemasok = $this->user->pemasok()->create($req->all());
      return $this->response->data($pemasok);
    } catch (Exception $e) {
      return $this->response->serverError();
    }
  }

  public function detailPemasok($id) {
    try {
      $pemasok = $this->user->pemasok()->findOrFail($id);
      return $this->response->data($pemasok);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Pemasok tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

  public function editPemasok(Request $req, $id) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $pemasok = $this->user->pemasok()->findOrFail($id);
      $pemasok->update($req->all());
      return $this->response->data($pemasok);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Pemasok tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

}
