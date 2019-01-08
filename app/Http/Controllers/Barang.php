<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang as BarangModel;
use Carbon\Carbon;
use Exception;

class Barang extends Controller {

  private $user;

  private $rule = [
    'nama' => 'required|string|max:100',
    'stok_minimal' => 'required|integer'
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listBarang() {
    return $this->response->data(BarangModel::orderBy('tanggal', 'desc')->get());
  }

  public function tambahBarang(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

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

  public function editBarang(Request $req, $id) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $barang = BarangModel::findOrFail($id);
      $barang->update($req->all());
      return $this->response->data($barang);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function hapusBarang($id) {
    try {
      $barang = BarangModel::findOrFail($id);
      $namaBarang = $barang->nama;
      $barang->delete();
      return $this->response->messageSuccess($namaBarang.' berhasil dihapus', 202);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

}
