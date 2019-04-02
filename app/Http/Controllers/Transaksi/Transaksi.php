<?php

namespace App\Http\Controllers\Transaksi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
    if ($invalid = $this->response->validate($req, ['jenis' => 'string|in:pembelian,penjualan', 'tanggal' => 'date'])) return $invalid;
    return $this->response->data(
      TransaksiModel::userId($this->user->id)->with(['pemasok', 'pelanggan'])->when($req->filled('jenis'), function($q) use ($req) {
        $q->where('jenis', $req->jenis);
      })->when($req->filled('tanggal'), function($q) use ($req) {
        $q->bulanTahun(new Carbon($req->tanggal));
      })->orderBy('tanggal', 'desc')->get()
    );
  }

  public function dataTransaksi($id) {
    return $this->response->data(TransaksiModel::userId($this->user->id)->with(['pemasok', 'pelanggan', 'barangTransaksi.barang', 'pelunasan'])->find($id));
  }

  private function transaksi(Request $req, $user_lain, $jenis) {
    if (!$user_lain) {
      $tipeUser = ($jenis == 'B') ? 'Pemasok':'Pelanggan';
      return $this->response->messageError("$tipeUser tidak ada", 404);
    }

    if ($req->filled('tgl')) $tanggal = new Carbon($req->tgl);
    else $tanggal = Carbon::now();
    $transaksi = ModulTransaksi::buatTransaksi($jenis, $req, $tanggal);
    $total = ModulTransaksi::totalTransaksiBarang($this->user, $jenis, $transaksi->id, $req->barang);
    $hutang = $total['total'];

    if ($total['total'] == 0) {
      $transaksi->delete();
      return $this->response->messageError('Total transaksi 0, transaksi dihapus.', 403);
    }

    if ($req->lunas) {
      $dataPelunasan = [
        'tanggal' => $tanggal,
        'nilai' => $total['total']
      ];
      if ($jenis == 'B') $dataPelunasan['debit'] = $total['total'];
      else $dataPelunasan['kredit'] = $total['total'];
      $pelunasan = $transaksi->pelunasan()->create($dataPelunasan);
      $hutang = 0;
      ModulTransaksi::keuangan($this->user, ['pelunasan_id' => $pelunasan->id], $jenis, $tanggal, $total['total'], 'pelunasan');
    }

    $dataTambahan = [
      'total' => $total['total'],
      'ph_utang' => $hutang,
      'beban_angkut' => $req->beban_angkut
    ];
    if ($jenis == 'B') $dataTambahan['pemasok_id'] = $user_lain->id;
    else $dataTambahan['pelanggan_id'] = $user_lain->id;
    $transaksi->update($dataTambahan);

    if ($req->beban_angkut > 0) {
      $kategori_beban = ($jenis == 'B') ? 'beban_pembelian':'beban_penjualan';
      ModulTransaksi::keuangan($this->user, ['transaksi_id' => $transaksi->id], 'B', $tanggal, $req->beban_angkut, $kategori_beban);
    }

    $this->logTransaksi($tanggal, $jenis, $req->lunas, $total, $req->beban_angkut);
    return $this->response->data(TransaksiModel::userId($this->user->id)->with(['pemasok', 'pelanggan'])->find($transaksi->id));
  }

  private function logTransaksi($tanggal, $jenis, $lunas, $total, $beban) {
    if ($jenis == 'B') {
      ModulTransaksi::logJurnal($this->user, $tanggal, '1', $total['total'], 'pembelian');
      ModulTransaksi::logJurnal($this->user, $tanggal, '3', $beban, 'beban_pembelian');
      if ($lunas) ModulTransaksi::logJurnal($this->user, $tanggal, '5', $total['total'], 'pelunasan_hutang');
    } else {
      ModulTransaksi::logJurnal($this->user, $tanggal, '2', $total['total'], 'penjualan');
      ModulTransaksi::logJurnal($this->user, $tanggal, '2.1', $total['hpp'], 'penjualan');
      ModulTransaksi::logJurnal($this->user, $tanggal, '4', $beban, 'beban_penjualan');
      if ($lunas) ModulTransaksi::logJurnal($this->user, $tanggal, '6', $total['total'], 'pelunasan_piutang');
    }
  }

  public function beli(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rulePembelian)) return $invalid;
    $pemasok = $this->user->pemasok()->find($req->pemasok_id);
    return $this->transaksi($req, $pemasok, 'B');
  }

  public function jual(Request $req) {
    if ($invalid = $this->response->validate($req, $this->rulePenjualan)) return $invalid;
    $pelanggan = $this->user->pelanggan()->find($req->pelanggan_id);
    return $this->transaksi($req, $pelanggan, 'J');
  }

  public function pelunasan(Request $req, $id) {
    $transaksi = TransaksiModel::userId($this->user->id)->find($id);
    if (!$transaksi) return $this->response->messageError('Transaksi tidak ditemukan', 404);
    if ($invalid = $this->response->validate($req, $this->rulePelunasan)) return $invalid;
    if ($transaksi->ph_utang == 0) return $this->response->messageError('Sudah tidak ada utang', 403);
    if ($transaksi->ph_utang < $req->nilai) return $this->response->messageError('Kelebihan nilai', 403);

    if ($req->filled('tgl')) $tanggal = new Carbon($req->tgl);
    else $tanggal = Carbon::now();
    $saldo = $transaksi->ph_utang - $req->nilai;
    if ($transaksi->jenis == 'pembelian') $req->merge(['debit' => $req->nilai]);
    else $req->merge(['kredit' => $req->nilai]);
    $req->merge(['saldo' => $saldo, 'tanggal' => $tanggal]);

    $pelunasan = $transaksi->pelunasan()->create($req->except('user', 'tgl'));
    $transaksi->update(['ph_utang' => $saldo]);

    $jenis = ($transaksi->jenis == 'pembelian') ? 'B':'J';
    ModulTransaksi::keuangan($this->user, ['pelunasan_id' => $pelunasan->id], $jenis, $tanggal, $req->nilai, 'pelunasan');

    if ($jenis == 'B') ModulTransaksi::logJurnal($this->user, $tanggal, '5', $req->nilai, 'pelunasan_hutang');
    else ModulTransaksi::logJurnal($this->user, $tanggal, '6', $req->nilai, 'pelunasan_piutang');

    return $this->response->data($transaksi->pelunasan()->find($pelunasan->id));
  }
}
