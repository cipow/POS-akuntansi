<?php

namespace App\Http\Controllers\Transaksi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Transaksi\Transaksi;
use Carbon\Carbon;

class Laporan extends Controller {

  private $user;

  private $ruleBulanan = [
    'tanggal' => 'required|date',
    'pembelian' => 'required|integer',
    'penjualan' => 'required|integer',
    'beban_angkut.pembelian' => 'required|integer',
    'beban_angkut.penjualan' => 'required|integer',
    'beban_angkut.gaji' => 'required|integer',
    'beban_angkut.operasional' => 'required|integer',
    'beban_angkut.pajak' => 'required|integer',
    'persediaan.awal' => 'required|integer',
    'persediaan.akhir' => 'required|integer',
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  private function persediaan(Carbon $tanggal) {
    $barangs = Barang::has('barangTransaksi')->get();
    $tanggalSebelumnya = new Carbon($tanggal);
    $tanggalSebelumnya->subMonth();
    $awal = 0; $akhir = 0;

    foreach ($barangs as $barang) {
      $awal += $this->hitung($barang, $tanggalSebelumnya);
      $akhir += $this->hitung($barang, $tanggal);
    }

    return ['awal' => $awal, 'akhir' => $akhir];
  }

  private function hitung($barang, $tanggal) {
    $saldoRP = $barang->barangTransaksi()->transaksiTanggal($tanggal)->first();
    if (!$saldoRP) {
      $saldoRPSebelum = $barang->barangTransaksi()->transaksiTanggalSebelumnya($tanggal)->first();
      return ($saldoRPSebelum) ? $saldoRPSebelum->saldo_rp:0;
    }
    return $saldoRP->saldo_rp;
  }

  public function laporanBulanan(Request $req) {
    if ($invalid = $this->response->validate($req, ['tanggal' => 'required|date'])) return $invalid;
    $tanggal = new Carbon($req->tanggal);
    $pembelian = Transaksi::laporanTransaksi($tanggal, 'pembelian')->sum('total');
    $penjualan = Transaksi::laporanTransaksi($tanggal, 'penjualan')->sum('total');
    $beban_angkut = [
      'pembelian' => (int) Transaksi::laporanTransaksi($tanggal, 'pembelian')->sum('beban_angkut'),
      'penjualan' => (int) Transaksi::laporanTransaksi($tanggal, 'penjualan')->sum('beban_angkut')
    ];


    return $this->response->data([
      'pembelian' => (int) $pembelian,
      'penjualan' => (int) $penjualan,
      'beban_angkut' => $beban_angkut,
      'persediaan' => $this->persediaan($tanggal)
    ]);
  }

  public function simpanLaporanBulanan(Request $req) {
    if ($invalid = $this->response->validate($req, $this->ruleBulanan)) return $invalid;
    $beban_angkut = (object) $req->beban_angkut;
    $persediaan = (object) $req->persediaan;

    $laba_rugi = $req->pembelian + $req->penjualan;
    $laba_rugi = $laba_rugi + $beban_angkut->pembelian + $beban_angkut->penjualan + $beban_angkut->gaji + $beban_angkut->operasional + $beban_angkut->pajak;
    $laba_rugi = $laba_rugi + $persediaan->awal + $persediaan->akhir;
    $req->merge(['laba_rugi' => $laba_rugi]);
    return $this->response->data($req->except('user'));
  }
}
