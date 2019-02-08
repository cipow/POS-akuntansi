<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Exception;

class Penjualan extends Controller {

  private $user;

  private $rulePenjualan = [
    'nofaktur' => 'required|string|max:191',
    'tgl_jual' => 'required|date',
    'tgl_tempo'=> 'required|date',
    'jenis_pembayaran' => 'required|string',
    'jmlh_jual' => 'required|integer',
    'harga_jual'=> 'required|integer',
    'pelanggan_id'=> 'required|integer'
  ];

  private $rulePembayaran = [
    'tanggal' => 'required|date',
    'nilai' => 'required|integer',
    'keterangan' => 'string'
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function listPenjualan($barang_id) {
    try {
      return $this->response->data(
        Barang::findOrFail($barang_id)->transaksi()->has('penjualan')->with('penjualan')->orderBy('tanggal', 'desc')->paginate(10)
      );
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function getPenjualan($barang_id, $id) {
    try {
      return $this->response->data(Barang::findOrFail($barang_id)->transaksi()->with(['penjualan' => function($q) {
        $q->with(['pelanggan', 'piutang']);
      }])->findOrFail($id));
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang/Transaksi tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function add(Request $req, $barang_id) {
    if ($invalid = $this->response->validate($req, $this->rulePenjualan)) return $invalid;

    try {
      $barang = Barang::findOrFail($barang_id);
      $pemasok = Pelanggan::findOrFail($req->pelanggan_id);
      $transaksi_pembelian = $barang->transaksi()->has('pembelian')->get();

      if ($transaksi_pembelian->isEmpty()) return $this->response->messageError('belum pernah melakukan pembelian', 403);
      if ($barang->stok == 0) return $this->response->messageError('stok kosong', 403);
      if ($barang->stok < $req->jmlh_jual) return $this->response->messageError('jumlah stok kurang untuk dijual', 403);

      $harga_satuan = $req->harga_jual/$req->jmlh_jual;
      $harga_rata = ($barang->harga_rata + $harga_satuan)/2;
      $total_kg = $barang->stok - $req->jmlh_jual;
      $total_harga_rata = $harga_rata * $total_kg;

      $barang->update([
        'stok' => $total_kg,
        'harga_rata' => $harga_rata
      ]);

      $penjualan = $barang->transaksi()->create([
        'tanggal' => $req->tgl_jual,
        'nofaktur_penjualan' => $req->nofaktur,
        'keluar_kg' => $req->jmlh_jual,
        'harga_jual' => $harga_satuan,
        'total_penjualan' => $req->harga_jual,
        'saldo_kg' => $total_kg,
        'harga_rata' => $harga_rata,
        'saldo_rp' => $total_harga_rata
      ])->penjualan()->create([
        'pelanggan_id' => $req->pelanggan_id,
        'tanggal' => $req->tgl_jual,
        'nofaktur' => $req->nofaktur,
        'jenis_pembayaran' => $req->jenis_pembayaran,
        'tanggal_tempo' => $req->tgl_tempo,
        'jumlah' => $req->jmlh_jual,
        'harga' => $harga_satuan,
        'total' => $req->harga_jual,
        'total_piutang' => $req->harga_jual
      ]);

      return $this->response->data($barang->transaksi()->with('penjualan.pelanggan')->find($penjualan->transaksi->id));
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang/Pelanggan tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function pembayaran(Request $req, $barang_id, $id) {
    if ($invalid = $this->response->validate($req, $this->rulePembayaran)) return $invalid;

    try {
      $penjualan = Barang::findOrFail($barang_id)->transaksi()->has('penjualan')->findOrFail($id)->penjualan;
      if ($penjualan->total_piutang < $req->nilai) return $this->response->messageError('Kelebihan Nilai', 403);
      $pembayaran = $penjualan->piutang()->create([
        'tanggal' => $req->tanggal,
        'nilai' => $req->nilai,
        'keterangan' => $req->keterangan,
        'kredit' => $req->nilai,
        'saldo' => $penjualan->total_piutang - $req->nilai
      ]);
      $penjualan->update(['total_piutang' => $penjualan->total_piutang - $req->nilai]);

      return $this->response->data($penjualan->piutang()->find($pembayaran->id));
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Barang/Transaksi tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

  public function hapusPembayaran(Request $req, $barang_id, $transaksi_id, $id) {
    try {
      $penjualan = Barang::findOrFail($barang_id)->transaksi()->findOrFail($transaksi_id)->penjualan;
      $piutang = $penjualan->piutang()->findOrFail($id);

      $penjualan->increment('total_piutang', $piutang->nilai);
      $piutang->delete();

      return $this->response->messageSuccess('Berhasil dihapus', 200);
    } catch (Exception $e) {
      if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        return $this->response->messageError('Data tidak ditemukan', 404);

      return $this->response->serverError();
    }

  }

}
