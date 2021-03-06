<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;

class Barang extends Controller {

  private $user;

  private $rule = [
    'kode' => 'required|string',
    'nama' => 'required|string|max:100',
    'stok_minimal' => 'required|integer'
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listBarang() {
    return $this->response->data($this->user->barang()->orderBy('tanggal', 'desc')->get());
  }

  public function tambahBarang(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $barang_sama = $this->user->barang()->where('kode', $req->kode)->first();
      if ($barang_sama) return $this->response->messageError('Kode sudah digunakan', 403);
      if ($req->filled('tgl')) $tanggal = new Carbon($req->tgl);
      else $tanggal = Carbon::now();
      $req->merge(['tanggal' => $tanggal]);
      $barang = $this->user->barang()->create($req->all());
      return $this->response->data($this->user->barang()->find($barang->id));
    } catch (Exception $e) {
      return $this->response->serverError();
    }
  }

  public function detailBarang($id) {
    try {
      $barang = $this->user->barang()->findOrFail($id);
      return $this->response->data($barang);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function detailBarangTransaksi($id) {
    try {
      $barang = $this->user->barang()->findOrFail($id);
      $transaksi = $barang->barangTransaksi()->with('transaksi')->orderBy('id', 'desc')->get();
      return $this->response->data($transaksi);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }
  }

  public function editBarang(Request $req, $id) {
    if ($invalid = $this->response->validate($req, $this->rule)) return $invalid;

    try {
      $barang = $this->user->barang()->findOrFail($id);
      if ($barang->kode != $req->kode) {
        $barang_sama = $this->user->barang()->where('kode', $req->kode)->first();
        if ($barang_sama) return $this->response->messageError('Kode sudah digunakan', 403);
      }
      $barang->update($req->all());
      return $this->response->data($barang);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  // public function hapusBarang($id) {
  //   try {
  //     $barang = BarangModel::findOrFail($id);
  //     $namaBarang = $barang->nama;
  //     $barang->delete();
  //     return $this->response->messageSuccess($namaBarang.' berhasil dihapus', 202);
  //   } catch (Exception $e) {
  //     if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
  //       return $this->response->messageError('Barang tidak ditemukan', 404);
  //
  //     return $this->response->serverError();
  //   }
  // }

}
