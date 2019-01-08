<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang as BarangModel;
use Carbon\Carbon;
use Exception;

class Barang extends Controller {

  private $user;

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listBarang() {
    return $this->response->data(BarangModel::all());
  }

  public function tambahBarang(Request $req) {
    $rule = [
      'nama' => 'required|string|max:100',
      'stok_minimal' => 'required|integer'
    ];

    if ($invalid = $this->response->validate($req, $rule)) return $invalid;

    try {
      $req->merge(['tanggal' => Carbon::now()]);
      $barang = BarangModel::create($req->all());
      return $this->response->data(BarangModel::find($barang->id));
    } catch (Exception $e) {
      return $this->response->serverError();
    }
  }

  public function detailBarang($id) {
    try {
      $barang = BarangModel::findOrFail($id);
      return $this->response->data($barang);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

}
