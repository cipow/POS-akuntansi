<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pemasok;
use Carbon\Carbon;
use Exception;

class Pembelian extends Controller {

  private $user;

  private $rulePembelian = [
    'nofaktur' => 'required|string|max:191',
    'tgl_beli' => 'required|date',
    'tgl_tempo'=> 'required|date',
    'jenis_pembayaran' => 'required|string',
    'jmlh_beli' => 'required|integer',
    'harga_beli'=> 'required|integer',
    'pemasok_id'=> 'required|integer'
  ];

  private $rulePelunasan = [
    'tanggal' => 'required|date',
    'nilai' => 'required|integer',
    'keterangan' => 'string'
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listPembelian($barang_id) {
    try {
      return $this->response->data(
        Barang::findOrFail($barang_id)->transaksi()->has('pembelian')->with('pembelian')->orderBy('tanggal', 'desc')->paginate(10)
      );
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function getPembelian($barang_id, $id) {
    try {
      return $this->response->data(Barang::findOrFail($barang_id)->transaksi()->with(['pembelian' => function($q) {
        $q->with(['pemasok', 'hutang']);
      }])->findOrFail($id));
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang/Transaksi tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function add(Request $req, $barang_id) {
    if ($invalid = $this->response->validate($req, $this->rulePembelian)) return $invalid;

    try {
      $barang = Barang::findOrFail($barang_id);
      $pemasok = Pemasok::findOrFail($req->pemasok_id);

      $harga_satuan = $req->harga_beli/$req->jmlh_beli;
      $harga_rata = ($barang->harga_rata == 0) ? $harga_satuan: ($barang->harga_rata + $harga_satuan)/2;
      $total_kg = $barang->stok + $req->jmlh_beli;
      $total_harga_rata = $harga_rata * $total_kg;

      $barang->update([
        'stok' => $total_kg,
        'harga_rata' => $harga_rata
      ]);

      $pembelian = $barang->transaksi()->create([
        'tanggal' => $req->tgl_beli,
        'nofaktur_pembelian' => $req->nofaktur,
        'masuk_kg' => $req->jmlh_beli,
        'harga_beli' => $harga_satuan,
        'total_pembelian' => $req->harga_beli,
        'saldo_kg' => $total_kg,
        'harga_rata' => $harga_rata,
        'saldo_rp' => $total_harga_rata
      ])->pembelian()->create([
        'pemasok_id' => $req->pemasok_id,
        'tanggal' => $req->tgl_beli,
        'nofaktur' => $req->nofaktur,
        'jenis_pembayaran' => $req->jenis_pembayaran,
        'tanggal_tempo' => $req->tgl_tempo,
        'jumlah' => $req->jmlh_beli,
        'harga' => $harga_satuan,
        'total' => $req->harga_beli,
        'total_hutang' => $req->harga_beli
      ]);

      return $this->response->data($barang->transaksi()->with('pembelian.pemasok')->find($pembelian->transaksi->id));
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang/Pemasok tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function pelunasan(Request $req, $barang_id, $id) {
    if ($invalid = $this->response->validate($req, $this->rulePelunasan)) return $invalid;

    try {
      $pembelian = Barang::findOrFail($barang_id)->transaksi()->has('pembelian')->findOrFail($id)->pembelian;
      if ($pembelian->total_hutang < $req->nilai) return $this->response->messageError('Kelebihan Nilai', 403);
      $pelunasan = $pembelian->hutang()->create([
        'tanggal' => $req->tanggal,
        'nilai' => $req->nilai,
        'keterangan' => $req->keterangan,
        'debit' => $req->nilai,
        'saldo' => $pembelian->total_hutang - $req->nilai
      ]);
      $pembelian->update(['total_hutang' => $pembelian->total_hutang - $req->nilai]);

      return $this->response->data($pembelian->hutang()->find($pelunasan->id));
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang/Transaksi tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function hapusPelunasan(Request $req, $barang_id, $transaksi_id, $id) {
    try {
      $pembelian = Barang::findOrFail($barang_id)->transaksi()->findOrFail($transaksi_id)->pembelian;
      $hutang = $pembelian->hutang()->findOrFail($id);

      $pembelian->increment('total_hutang', $hutang->nilai);
      $hutang->delete();

      return $this->response->messageSuccess('Berhasil dihapus', 200);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Data tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

}
