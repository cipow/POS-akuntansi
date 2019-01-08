<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan as PelangganModel;
use Carbon\Carbon;
use Exception;

class Pelanggan extends Controller {

  private $user;

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listPelanggan() {
    return $this->response->data(PelangganModel::all());
  }

  public function tambahPelanggan(Request $req) {
    $rule = [
      'nama' => 'required|string|max:100',
      'email' => 'string|email|max:100',
      'telepon' => 'required|string|max:20',
      'alamat' => 'string',
    ];

    if ($invalid = $this->response->validate($req, $rule)) return $invalid;

    try {
      $pelanggan = PelangganModel::create($req->all());
      return $this->response->data($pelanggan);
    } catch (Exception $e) {
      return $this->response->serverError();
    }
  }

  public function detailPelanggan($id) {
    try {
      $pelanggan = PelangganModel::findOrFail($id);
      return $this->response->data($pelanggan);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Pelanggan tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

}
