<?php

namespace App\Http\Controllers\Transaksi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Transaksi\Transaksi;
use App\Models\Laporan\Bulanan;
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

  public function dataLaporan(Request $req) {
    $rule = [
      'jenis' => 'string|in:bulan,tahun',
      'tanggal' => 'date'
    ];
    if ($invalid = $this->response->validate($req, $rule)) return $invalid;
    $tanggal = new Carbon($req->tanggal);
    $data['meta'] = $req->query();
    unset($data['meta']['user']);

    if ($req->filled('jenis')) {
      if ($req->jenis == 'bulan') {
        $data['bulan'] = Bulanan::when($req->filled('tanggal'), function($q) use ($tanggal) {
          $q->bulanTahun($tanggal);
        })->orderBy('tanggal', 'desc')->get();
      } else {

      }
    } else {
      $data['bulan'] = Bulanan::orderBy('tanggal', 'desc')->get();
      $data['tahun'] = NULL;
    }

    return $this->response->data($data);
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
    if ($invalid = $this->response->validate($req, ['tanggal' => 'date'])) return $invalid;
    if ($req->filled('tanggal')) {
        $tanggalBefore = new Carbon($req->tanggal);
        $tanggal = new Carbon("$tanggalBefore->year-$tanggalBefore->month");
    }
    else {
      $lpBulan = Bulanan::orderBy('tanggal_laporan', 'desc')->first();
      if ($lpBulan) {
          $tgl = $lpBulan->tanggal_laporan;
          $drTransaksi = true;
      }
      else {
        $transaksi = Transaksi::orderBy('tanggal', 'asc')->first();
        $tgl = $transaksi->tanggal;
        $drTransaksi = false;
      }
      $tanggalBefore = new Carbon($tgl);
      $tanggal = new Carbon("$tanggalBefore->year-$tanggalBefore->month");
      if ($drTransaksi) $tanggal->addMonth();
    }

    $pembelian = Transaksi::laporanTransaksi($tanggal, 'pembelian')->sum('total');
    $penjualan = Transaksi::laporanTransaksi($tanggal, 'penjualan')->sum('total');
    $beban_angkut = [
      'pembelian' => (int) Transaksi::laporanTransaksi($tanggal, 'pembelian')->sum('beban_angkut'),
      'penjualan' => (int) Transaksi::laporanTransaksi($tanggal, 'penjualan')->sum('beban_angkut')
    ];

    $laporan_bulanan = Bulanan::whereYear('tanggal_laporan', $tanggal->year)->whereMonth('tanggal_laporan', $tanggal->month)->first();
    if ($laporan_bulanan) $sudah = true;
    else $sudah = false;

    return $this->response->data([
      'tanggal' => "$tanggal->year-$tanggal->month-$tanggal->day",
      'pembelian' => (int) $pembelian,
      'penjualan' => (int) $penjualan,
      'beban_angkut' => $beban_angkut,
      'persediaan' => $this->persediaan($tanggal),
      'sudah' => $sudah
    ]);
  }

  public function simpanLaporanBulanan(Request $req) {
    if ($invalid = $this->response->validate($req, $this->ruleBulanan)) return $invalid;
    $beban_angkut = (object) $req->beban_angkut;
    $persediaan = (object) $req->persediaan;

    $tgl = new Carbon($req->tanggal);
    $laporan_bulanan = Bulanan::whereYear('tanggal_laporan', $tgl->year)->whereMonth('tanggal_laporan', $tgl->month)->first();
    if ($laporan_bulanan) return $this->response->messageError('Laporan sudah dibuat', 403);

    $penjualan = $req->penjualan;
    $harga_pokok_penjualan = $persediaan->awal + $req->pembelian + $beban_angkut->pembelian - $persediaan->akhir;
    $laba_kotor = $penjualan - $harga_pokok_penjualan;
    $beban = $beban_angkut->gaji + $beban_angkut->operasional + $beban_angkut->penjualan + $beban_angkut->pajak;
    $laba_bersih = $laba_kotor - $beban;

    $tanggal = Carbon::now();

    $laporan = Bulanan::create([
      'tanggal' => $tanggal,
      'tanggal_laporan' => $req->tanggal,
      'penjualan' => $req->penjualan,
      'pembelian' => $req->pembelian,
      'persediaan_awal' => $persediaan->awal,
      'persediaan_akhir' => $persediaan->akhir,
      'beban_penjualan' => $beban_angkut->penjualan,
      'beban_pembelian' => $beban_angkut->pembelian,
      'beban_gaji' => $beban_angkut->gaji,
      'beban_operasional' => $beban_angkut->operasional,
      'beban_pajak' => $beban_angkut->pajak,
      'laba_kotor' => $laba_kotor,
      'laba_bersih' => $laba_bersih
    ]);

    return $this->response->data(Bulanan::find($laporan->id));
  }
}
