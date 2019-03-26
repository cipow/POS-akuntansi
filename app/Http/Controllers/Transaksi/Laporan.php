<?php

namespace App\Http\Controllers\Transaksi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
    'depresiasi.bangunan' => 'required|integer',
    'depresiasi.kendaraan' => 'required|integer',
    'depresiasi.peralatan' => 'required|integer'
  ];

  private $ruleModal = [
    'tanggal' => 'required|date',
    'awal' => 'required|integer',
    'akhir' => 'required|integer',
    'range_tanggal' => 'required|string',
    'total_laba_bersih' => 'required|integer',
    'total_prive' => 'required|integer'
  ];

  private $ruleKas = [
    'tanggal' => 'required|date',
    'pelunasan.piutang' => 'required|integer',
    'pelunasan.hutang' => 'required|integer',
    'beban.angkut' => 'required|integer',
    'beban.gaji' => 'required|integer',
    'beban.operasional' => 'required|integer',
    'beban.pajak' => 'required|integer',
    'total_operasi' => 'required|integer',
    'asset.tanah' => 'required|integer',
    'asset.perlengkapan' => 'required|integer',
    'asset.bangunan' => 'required|integer',
    'asset.kendaraan' => 'required|integer',
    'asset.peralatan' => 'required|integer',
    'total_investasi' => 'required|integer',
    'prive' => 'required|integer',
    'kenaikan_saldo' => 'required|integer',
    'saldo_awal' => 'required|integer',
    'saldo_akhir' => 'required|integer'
  ];

  private $ruleNeraca = [
    'tanggal' => 'required|date',
    'tanggal_laporan' => 'required|date',
    'kas' => 'required|integer',
    'modal' => 'required|integer',
    'persediaan_akhir' => 'required|integer',
    'hutang' => 'required|integer',
    'piutang' => 'required|integer',
    'tanah' => 'required|integer',
    'perlengkapan' => 'required|integer',
    'bangunan' => 'required|integer',
    'depresiasi_bangunan' => 'required|integer',
    'peralatan' => 'required|integer',
    'depresiasi_peralatan' => 'required|integer',
    'kendaraan' => 'required|integer',
    'depresiasi_kendaraan' => 'required|integer',
    'aktiva' => 'required|integer',
    'passiva' => 'required|integer'
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
        $data['bulan'] = $this->user->lpBulan()->when($req->filled('tanggal'), function($q) use ($tanggal) {
          $q->bulanTahun($tanggal);
        })->orderBy('tanggal', 'desc')->get();
      } else {

      }
    } else {
      $data['bulan'] = $this->user->lpBulan()->orderBy('tanggal', 'desc')->get();
      $data['tahun'] = NULL;
    }

    return $this->response->data($data);
  }

  private function persediaan(Carbon $tanggal) {
    $barangs = $this->user->barang()->has('barangTransaksi')->get();
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
      $lpBulan = $this->user->lpBulan()->orderBy('tanggal_laporan', 'desc')->first();
      if ($lpBulan) {
          $tgl = $lpBulan->tanggal_laporan;
          $drLaporan = true;
      }
      else {
        $transaksi = Transaksi::userId($this->user->id)->orderBy('tanggal', 'asc')->first();
        $tgl = $transaksi->tanggal;
        $drLaporan = false;
      }
      $tanggalBefore = new Carbon($tgl);
      $tanggal = new Carbon("$tanggalBefore->year-$tanggalBefore->month");
      if ($drLaporan) $tanggal->addMonth();
    }

    $pembelian = Transaksi::userId($this->user->id)->laporanTransaksi($tanggal, 'pembelian')->sum('total');
    $penjualan = Transaksi::userId($this->user->id)->laporanTransaksi($tanggal, 'penjualan')->sum('total');
    $beban_angkut = [
      'pembelian' => (int) Transaksi::userId($this->user->id)->laporanTransaksi($tanggal, 'pembelian')->sum('beban_angkut'),
      'penjualan' => (int) Transaksi::userId($this->user->id)->laporanTransaksi($tanggal, 'penjualan')->sum('beban_angkut')
    ];

    $depresiasi = [
      'bangunan' => (int) $this->user->asset()->laporanKas($tanggal, 'bangunan')->sum('nilai_sekarang'),
      'kendaraan' => (int) $this->user->asset()->laporanKas($tanggal, 'kendaraan')->sum('nilai_sekarang'),
      'peralatan' => (int) $this->user->asset()->laporanKas($tanggal, 'peralatan')->sum('nilai_sekarang')
    ];

    $laporan_bulanan = $this->user->lpBulan()->whereYear('tanggal_laporan', $tanggal->year)->whereMonth('tanggal_laporan', $tanggal->month)->first();
    if ($laporan_bulanan) $sudah = true;
    else $sudah = false;

    return $this->response->data([
      'tanggal' => "$tanggal->year-$tanggal->month-$tanggal->day",
      'pembelian' => (int) $pembelian,
      'penjualan' => (int) $penjualan,
      'beban_angkut' => $beban_angkut,
      'persediaan' => $this->persediaan($tanggal),
      'depresiasi' => $depresiasi,
      'sudah' => $sudah
    ]);
  }

  public function simpanLaporanBulanan(Request $req) {
    if ($invalid = $this->response->validate($req, $this->ruleBulanan)) return $invalid;
    $beban_angkut = (object) $req->beban_angkut;
    $persediaan = (object) $req->persediaan;
    $depresiasi = (object) $req->depresiasi;

    $tgl = new Carbon($req->tanggal);
    $laporan_bulanan = $this->user->lpBulan()->bulanTahun($tgl)->first();
    if ($laporan_bulanan) return $this->response->messageError('Laporan sudah dibuat', 403);

    $penjualan = $req->penjualan;
    $harga_pokok_penjualan = $persediaan->awal + $req->pembelian + $beban_angkut->pembelian - $persediaan->akhir;
    $laba_kotor = $penjualan - $harga_pokok_penjualan;
    $beban = $beban_angkut->gaji + $beban_angkut->operasional + $beban_angkut->penjualan + $beban_angkut->pajak;
    $laba_bersih = $laba_kotor - $beban;

    $tanggal = Carbon::now();

    $laporan = $this->user->lpBulan()->create([
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
      'depresiasi_bangunan' => $depresiasi->bangunan,
      'depresiasi_kendaraan' => $depresiasi->kendaraan,
      'depresiasi_peralatan' => $depresiasi->peralatan,
      'laba_kotor' => $laba_kotor,
      'laba_bersih' => $laba_bersih
    ]);

    ModulTransaksi::keuangan($this->user, ['lp_bulan_id' => $laporan->id], 'B', $tanggal, $beban_angkut->gaji, 'beban_gaji');
    ModulTransaksi::keuangan($this->user, ['lp_bulan_id' => $laporan->id], 'B', $tanggal, $beban_angkut->operasional, 'beban_operasional');
    ModulTransaksi::keuangan($this->user, ['lp_bulan_id' => $laporan->id], 'B', $tanggal, $beban_angkut->pajak, 'beban_pajak');

    return $this->response->data($this->user->lpBulan()->find($laporan->id));
  }

  public function laporanModals(Request $req) {
    if ($this->user->lpBulan()->get()->isEmpty()) return $this->response->messageError('Laporan Laba Bersih belum ada', 403);
    if ($invalid = $this->response->validate($req, ['tanggal' => 'date'])) return $invalid;
    if ($req->filled('tanggal')) {
        $tanggalBefore = new Carbon($req->tanggal);
        $tanggal = new Carbon("$tanggalBefore->year-$tanggalBefore->month");
    }
    else {
      $perubahanModal = $this->user->perubahanModal()->orderBy('tanggal', 'desc')->first();
      if ($perubahanModal) {
          $tgl = $perubahanModal->tanggal;
          $drLaporan = true;
      }
      else {
        $lpBulan = $this->user->lpBulan()->orderBy('tanggal', 'asc')->first();
        $tgl = $lpBulan->tanggal;
        $drLaporan = false;
      }
      $tanggalBefore = new Carbon($tgl);
      $tanggal = new Carbon("$tanggalBefore->year-$tanggalBefore->month");
      if ($drLaporan) $tanggal->addMonth();
    }

    $dataPerubahanModal = $this->user->perubahanModal()->bulanTahun($tanggal)->first();
    if ($dataPerubahanModal) $sudah = true;
    else $sudah = false;

    $range_tanggal = "$tanggal->year-$tanggal->month - $tanggal->year-$tanggal->month";
    $total_laba_bersih = $this->user->lpBulan()->bulanTahun($tanggal)->sum('laba_bersih');
    $total_prive = $this->user->keuangan()->laporanKas($tanggal, 'prive')->sum('nilai');
    $modal_awal = $this->user->modal;
    $modal_akhir = $modal_awal + ($total_laba_bersih - $total_prive);
    return $this->response->data([
      'tanggal' => "$tanggal->year-$tanggal->month-$tanggal->day",
      'awal' => $modal_awal,
      'akhir' => $modal_akhir,
      'range_tanggal' => $range_tanggal,
      'total_laba_bersih' => (int) $total_laba_bersih,
      'total_prive' => (int) $total_prive,
      'sudah' => $sudah
    ]);
  }

  public function laporanModal(Request $req) {
    if ($this->user->lpBulan()->get()->isEmpty()) return $this->response->messageError('Laporan Laba Bersih belum ada', 403);
    $tanggal = ($req->filled('tanggal')) ? new Carbon($req->tanggal):Carbon::now();
    $tanggalModal = new Carbon("$tanggal->year-$tanggal->month");
    $tanggalAkhir = new Carbon($tanggalModal);
    $tanggalAkhir->subDay();

    $modalTanggalAkhir = $this->user->perubahanModal()->bulanTahun($tanggalModal)->first();
    if ($modalTanggalAkhir) $sudah = true;
    else $sudah = false;

    if ($modal = $this->user->perubahanModal()->orderBy('tanggal', 'desc')->first()) {
      $tanggalMulai = new Carbon($modal->tanggal);
      $tanggalMulai->addMonth();
    }
    else {
      $bulanan = $this->user->lpBulan()->orderBy('tanggal_laporan', 'asc')->first();
      $tanggalMulai = new Carbon($bulanan->tanggal_laporan);
    }

    $tanggal = [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()];
    $range_tanggal = "$tanggalMulai->year-$tanggalMulai->month - $tanggalAkhir->year-$tanggalAkhir->month";
    $total_laba_bersih = $this->user->lpBulan()->antaraTanggal($tanggal)->sum('laba_bersih');
    $total_prive = $this->user->keuangan()->where('kategori', 'prive')->antaraTanggal($tanggal)->sum('nilai');
    $modal_awal = $this->user->modal;
    $modal_akhir = $modal_awal + ($total_laba_bersih - $total_prive);

    return $this->response->data([
      'tanggal' => "$tanggalModal->year-$tanggalModal->month-$tanggalModal->day",
      'awal' => $modal_awal,
      'akhir' => $modal_akhir,
      'range_tanggal' => $range_tanggal,
      'total_laba_bersih' => (int) $total_laba_bersih,
      'total_prive' => (int) $total_prive,
      'sudah' => $sudah
    ]);
  }

  public function simpanLaporanModal(Request $req) {
    if ($invalid = $this->response->validate($req, $this->ruleModal)) return $invalid;

    $tanggal = new Carbon($req->tanggal);
    $modalTanggal = $this->user->perubahanModal()->bulanTahun($tanggal)->first();
    if ($modalTanggal) return $this->response->messageError('Laporan sudah dibuat', 403);

    $perubahanModal = $this->user->perubahanModal()->create($req->except('user'));
    $this->user->update(['modal' => $req->akhir]);
    return $this->response->data($this->user->perubahanModal()->find($perubahanModal->id));
  }

  public function riwayatLaporanModal() {
    return $this->response->data($this->user->perubahanModal()->orderBy('tanggal', 'desc')->get());
  }

  public function laporanKas(Request $req) {
    if ($invalid = $this->response->validate($req, ['tanggal' => 'date'])) return $invalid;
    if ($req->filled('tanggal')) {
      $tanggalBefore = new Carbon($req->tanggal);
      $tanggal = new Carbon("$tanggalBefore->year-$tanggalBefore->month");
    } else {
      $lpKas = $this->user->lpKas()->orderBy('tanggal_laporan', 'desc')->first();
      if ($lpKas) {
          $tgl = $lpKas->tanggal_laporan;
          $saldo_awal = $lpKas->saldo_akhir_bulan;
          $drLaporan = true;
      }
      else {
        $riwayatKeuangan = $this->user->keuangan()->orderBy('tanggal', 'asc')->first();
        $tgl = $riwayatKeuangan->tanggal;
        $saldo_awal = $riwayatKeuangan->nilai;
        $drLaporan = false;
      }
      $tanggalBefore = new Carbon($tgl);
      $tanggal = new Carbon("$tanggalBefore->year-$tanggalBefore->month");
      if ($drLaporan) $tanggal->addMonth();
    }

    $pelunasan = [
      'piutang' => (int) $this->user->keuangan()->laporanKas($tanggal, 'pelunasan')->where('jenis', 'masuk')->sum('nilai'),
      'hutang' => (int) $this->user->keuangan()->laporanKas($tanggal, 'pelunasan')->where('jenis', 'keluar')->sum('nilai')
    ];

    $beban = [
      'angkut' => (int) ($this->user->keuangan()->laporanKas($tanggal, 'beban_pembelian')->sum('nilai') + $this->user->keuangan()->laporanKas($tanggal, 'beban_penjualan')->sum('nilai')),
      'gaji' => (int) $this->user->keuangan()->laporanKas($tanggal, 'beban_gaji')->sum('nilai'),
      'operasional' => (int) $this->user->keuangan()->laporanKas($tanggal, 'beban_operasional')->sum('nilai'),
      'pajak' => (int) $this->user->keuangan()->laporanKas($tanggal, 'beban_pajak')->sum('nilai')
    ];

    $total_operasi = $pelunasan['piutang'] - $pelunasan['hutang'] - $beban['angkut'] - $beban['gaji'] - $beban['operasional'] - $beban['pajak'];

    $asset = [
      'tanah' => (int) $this->user->asset()->laporanKas($tanggal, 'tanah')->sum('harga_beli'),
      'perlengkapan' => (int) $this->user->asset()->laporanKas($tanggal, 'perlengkapan')->sum('harga_beli'),
      'bangunan' => (int) $this->user->asset()->laporanKas($tanggal, 'bangunan')->sum('harga_beli'),
      'kendaraan' => (int) $this->user->asset()->laporanKas($tanggal, 'kendaraan')->sum('harga_beli'),
      'peralatan' => (int) $this->user->asset()->laporanKas($tanggal, 'peralatan')->sum('harga_beli')
    ];

    $total_investasi = $asset['tanah'] + $asset['perlengkapan'] + $asset['bangunan'] + $asset['kendaraan'] + $asset['peralatan'];

    $prive = (int) $this->user->keuangan()->laporanKas($tanggal, 'prive')->sum('nilai');
    $kenaikan_saldo = $total_operasi - $total_investasi - $prive;
    $saldo_akhir = $saldo_awal + $kenaikan_saldo;

    $laporan_kas = $this->user->lpKas()->whereYear('tanggal_laporan', $tanggal->year)->whereMonth('tanggal_laporan', $tanggal->month)->first();
    if ($laporan_kas) $sudah = true;
    else $sudah = false;

    return $this->response->data([
      'tanggal' => "$tanggal->year-$tanggal->month-$tanggal->day",
      'pelunasan' => $pelunasan,
      'beban' => $beban,
      'total_operasi' => $total_operasi,
      'asset' => $asset,
      'total_investasi' => $total_investasi,
      'prive' => $prive,
      'kenaikan_saldo' => $kenaikan_saldo,
      'saldo_awal' => $saldo_awal,
      'saldo_akhir' => $saldo_akhir,
      'sudah' => $sudah
    ]);
  }

  public function simpanLaporanKas(Request $req) {
    if ($invalid = $this->response->validate($req, $this->ruleKas)) return $invalid;

    $tgl = new Carbon($req->tanggal);
    $laporan_kas = $this->user->lpKas()->bulanTahun($tgl)->first();
    if ($laporan_kas) return $this->response->messageError('Laporan sudah dibuat', 403);

    $pelunasan = (object) $req->pelunasan;
    $beban = (object) $req->beban;
    $asset = (object) $req->asset;

    $tanggal = Carbon::now();

    $laporan = $this->user->lpKas()->create([
      'tanggal' => $tanggal,
      'tanggal_laporan' => $req->tanggal,
      'pelunasan_piutang' => $pelunasan->piutang,
      'pelunasan_hutang' => $pelunasan->hutang,
      'beban_angkut' => $beban->angkut,
      'beban_gaji' => $beban->gaji,
      'beban_operasional' => $beban->operasional,
      'beban_pajak' => $beban->pajak,
      'total_operasi' => $req->total_operasi,
      'asset_tanah' => $asset->tanah,
      'asset_perlengkapan' => $asset->perlengkapan,
      'asset_bangunan' => $asset->bangunan,
      'asset_kendaraan' => $asset->kendaraan,
      'asset_peralatan' => $asset->peralatan,
      'total_investasi' => $req->total_investasi,
      'total_prive' => $req->prive,
      'kenaikan_saldo' => $req->kenaikan_saldo,
      'saldo_awal_bulan' => $req->saldo_awal,
      'saldo_akhir_bulan' => $req->saldo_akhir
    ]);

    return $this->response->data($this->user->lpKas()->find($laporan->id));
  }

  public function riwayatLaporanKas() {
    return $this->response->data($this->user->lpKas()->orderBy('tanggal', 'desc')->get());
  }

  public function laporanNeraca() {
    $tanggal = Carbon::now();
    $kas = $this->user->kas;
    $modal = $this->user->modal;
    $persediaan = $this->persediaan($tanggal);
    $hutang = Transaksi::userId($this->user->id)->where('jenis', 'pembelian')->sum('ph_utang');
    $piutang = Transaksi::userId($this->user->id)->where('jenis', 'penjualan')->sum('ph_utang');
    $tanah = $this->user->asset()->kategori('tanah')->sum('harga_beli');
    $perlengkapan = $this->user->asset()->kategori('perlengkapan')->sum('harga_beli');
    $bangunan = $this->user->asset()->kategori('bangunan')->sum('harga_beli');
    $depresiasi_bangunan = $this->user->asset()->kategori('bangunan')->belumKadaluarsa()->sum('nilai_sekarang');
    $peralatan = $this->user->asset()->kategori('peralatan')->sum('harga_beli');
    $depresiasi_peralatan = $this->user->asset()->kategori('peralatan')->belumKadaluarsa()->sum('nilai_sekarang');
    $kendaraan = $this->user->asset()->kategori('kendaraan')->sum('harga_beli');
    $depresiasi_kendaraan = $this->user->asset()->kategori('kendaraan')->belumKadaluarsa()->sum('nilai_sekarang');
    $aktiva = $kas + $piutang + $persediaan['akhir'] + $tanah + $perlengkapan + $bangunan + $peralatan + $kendaraan;
    $aktiva = $aktiva - $depresiasi_bangunan - $depresiasi_peralatan - $depresiasi_kendaraan;
    $passiva = $modal + $hutang;
    $tgl = "$tanggal->year-$tanggal->month-$tanggal->day";
    $time = "$tanggal->hour:$tanggal->minute:$tanggal->second";

    return $this->response->data([
      'tanggal' => "$tgl $time",
      'tanggal_laporan' => $tgl,
      'kas' => (int) $kas,
      'modal' => (int) $modal,
      'persediaan_akhir' => (int) $persediaan['akhir'],
      'hutang' => (int) $hutang,
      'piutang' => (int) $piutang,
      'tanah' => (int) $tanah,
      'perlengkapan' => (int) $perlengkapan,
      'bangunan' => (int) $bangunan,
      'depresiasi_bangunan' => (int) $depresiasi_bangunan,
      'peralatan' => (int) $peralatan,
      'depresiasi_peralatan' => (int) $depresiasi_peralatan,
      'kendaraan' => (int) $kendaraan,
      'depresiasi_kendaraan' => (int) $depresiasi_kendaraan,
      'aktiva' => (int) $aktiva,
      'passiva' => (int) $passiva
    ]);
  }

  public function simpanLaporanNeraca(Request $req) {
    if ($invalid = $this->response->validate($req, $this->ruleNeraca)) return $invalid;
    $neraca = $this->user->lpNeraca()->create($req->except('user'));

    $assets = $this->user->asset()->kategoriPenyusutan()->get();
    foreach ($assets as $asset) {
      $nilai_sekarang = $asset->nilai_sekarang + $asset->nilai_penyusutan;
      $asset->update(['nilai_sekarang' => $nilai_sekarang]);
    }
    
    return $this->response->data($neraca);
  }

  public function riwayatLaporanNeraca() {
    return $this->response->data($this->user->lpNeraca()->orderBy('tanggal', 'desc')->get());
  }
}
