<?php

namespace App\Http\Controllers\Transaksi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pemasok;
use App\Models\Pelanggan;
use App\Models\Transaksi\Transaksi as TransaksiModel;
use Carbon\Carbon;

class Transaksi extends Controller {

  private $user;

  private $rulePembelian = [
    'pemasok_id' => 'required|integer',
    'tanggal_tempo' => 'required|date',
    'beban_angkut' => 'required|integer',
    'lunas' => 'required|in:1,0',
    'barang' => 'required',
    'barang.*.id' => 'required|integer',
    'barang.*.jumlah' => 'required|integer',
    'barang.*.harga' => 'required|integer'
  ];

  private $rulePenjualan = [
    'pelanggan_id' => 'required|integer',
    'tanggal_tempo' => 'required|date',
    'beban_angkut' => 'required|integer',
    'lunas' => 'required|in:1,0',
    'barang' => 'required',
    'barang.*.id' => 'required|integer',
    'barang.*.jumlah' => 'required|integer',
    'barang.*.harga' => 'required|integer'
  ];

  private $ruleDaftarTransaksi = [
    'jenis' => 'string|in:pembelian,penjualan'
  ];

  private $rulePelunasan = [
    'nilai' => 'required|integer',
    'keterangan' => 'string'
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function daftarTransaksi(Request $req) {
    return $this->response->data(
      TransaksiModel::with(['pemasok', 'pelanggan'])->when($req->filled('jenis'), function($q) use ($req) {
        $q->where('jenis', $req->jenis);
      })->orderBy('tanggal', 'desc')->get()
    );
  }

  public function dataTransaksi($id) {
    return $this->response->data(TransaksiModel::with(['pemasok', 'pelanggan', 'barang.barang', 'pelunasan'])->find($id));
  }

  public function beli(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rulePembelian)) return $invalid;
    $pemasok = Pemasok::find($req->pemasok_id);
    if (!$pemasok) return $this->response->messageError('Pemasok tidak ada', 404);
    $tanggal = Carbon::now();
    $transaksi = ModulTransaksi::buatTransaksi('B', $req, $tanggal);
    $total = ModulTransaksi::totalTransaksiBarang('B', $transaksi->id, $req->barang);
    $hutang = $total;

    if ($req->lunas) {
      $transaksi->pelunasan()->create([
        'tanggal' => $tanggal,
        'nilai' => $total,
        'debit' => $total
      ]);
      $hutang = 0;
    }

    $transaksi->update([
      'pemasok_id' => $pemasok->id,
      'total' => $total,
      'ph_utang' => $hutang,
      'beban_angkut' => $req->beban_angkut
    ]);

    return $this->response->data($transaksi);
  }

  public function jual(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rulePenjualan)) return $invalid;
    $pelanggan = Pelanggan::find($req->pelanggan_id);
    if (!$pelanggan) return $this->response->messageError('Pelanggan tidak ada', 404);
    $tanggal = Carbon::now();
    $transaksi = ModulTransaksi::buatTransaksi('J', $req, $tanggal);
    $total = ModulTransaksi::totalTransaksiBarang('J', $transaksi->id, $req->barang);
    $hutang = $total;

    if ($req->lunas) {
      $transaksi->pelunasan()->create([
        'tanggal' => $tanggal,
        'nilai' => $total,
        'kredit' => $total
      ]);
      $hutang = 0;
    }

    $transaksi->update([
      'pelanggan_id' => $pelanggan->id,
      'total' => $total,
      'ph_utang' => $hutang,
      'beban_angkut' => $req->beban_angkut
    ]);

    return $this->response->data($transaksi);
  }

  public function pelunasan(Request $req, $id) {
    $transaksi = TransaksiModel::find($id);
    if (!$transaksi) return $this->response->messageError('Transaksi tidak ditemukan', 404);
    if ($invalid = $this->response->validate($req, $this->rulePelunasan)) return $invalid;
    if ($transaksi->ph_utang == 0) return $this->response->messageError('Sudah tidak ada utang', 403);
    if ($transaksi->ph_utang < $req->nilai) return $this->response->messageError('Kelebihan nilai', 403);

    $tanggal = Carbon::now();
    $saldo = $transaksi->ph_utang - $req->nilai;
    if ($transaksi->jenis == 'pembelian') $req->merge(['debit' => $req->nilai]);
    else $req->merge(['kredit' => $req->nilai]);
    $req->merge(['saldo' => $saldo, 'tanggal' => $tanggal]);

    $pelunasan = $transaksi->pelunasan()->create($req->except('user'));
    $transaksi->update(['ph_utang' => $saldo]);
    return $this->response->data($pelunasan);
  }
}
