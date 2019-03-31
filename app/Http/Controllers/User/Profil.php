<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Profil extends Controller {

  private $user;

  private $ruleRiwayatKeuangan = [
    'kategori' => 'string|in:prive,modal,pelunasan,beban_pembelian,beban_penjualan,beban_gaji,beban_operasional,beban_pajak'
  ];

  public function __construct(Request $req){
    parent::__construct();
    $this->user = $req->user;
  }

  public function index() {
    return $this->response->data($this->user);
  }

  public function modalAwal(Request $req) {
    if ($invalid = $this->response->validate($req, ['modal' => 'required|integer'])) return $invalid;
    if ($this->user->modal > 0) return $this->response->messageError('Sudah Input Modal', 403);
    $this->user->update(['modal' => $req->modal, 'kas' => $req->modal]);
    $this->user->keuangan()->create([
      'nilai' => $req->modal,
      'jenis' => 'masuk',
      'tanggal' => \Carbon\Carbon::now(),
      'kategori' => 'modal',
      'saldo_kas' => $req->modal,
      'keterangan' => 'Modal Awal'
    ]);
    return $this->response->messageSuccess('Berhasil tambah modal', 200);
  }

  public function riwayatKeuangan(Request $req) {
    if ($invalid = $this->response->validate($req, $this->ruleRiwayatKeuangan)) return $invalid;
    $riwayatKeuangan = $this->user->keuangan()->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->when($req->filled('kategori'), function($q) use ($req) {
      $q->where('kategori', $req->kategori);
    })->get();
    return $this->response->data($riwayatKeuangan);
  }

  public function detailKeuangan($id) {
    return $this->response->data($this->user->keuangan()->with(['transaksi', 'pelunasan', 'asset', 'lpBulan'])->find($id));
  }

  public function prive(Request $req) {
    if ($invalid = $this->response->validate($req, ['prive' => 'required|integer', 'keterangan' => 'string'])) return $invalid;
    if ($this->user->kas == 0) return $this->response->messageError('Kas kosong', 403);
    if ($this->user->kas < $req->prive) return $this->response->messageError('Uang Kas kurang', 403);
    $tanggal = \Carbon\Carbon::now();
    $sisa_kas = $this->user->kas - $req->prive;
    $this->user->update(['kas' => $sisa_kas]);
    $this->user->keuangan()->create([
      'nilai' => $req->prive,
      'jenis' => 'keluar',
      'tanggal' => $tanggal,
      'kategori' => 'prive',
      'saldo_kas' => $sisa_kas,
      'keterangan' => $req->keterangan
    ]);

    $this->user->jurnal()->create([
      'tanggal' => $tanggal,
      'kode' => '7',
      'nilai' => $req->prive,
      'keterangan' => 'prive'
    ]);
    return $this->response->messageSuccess('Prive Berhasil', 200);
  }

  public function jurnal(Request $req) {
    if ($invalid = $this->response->validate($req, ['tanggal' => 'date'])) return $invalid;
    $jurnal = $this->user->jurnal();
    if ($req->filled('tanggal')) $jurnal->bulanTahun(new \Carbon\Carbon($req->tanggal));
    $jurnal->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
    return $this->response->data($jurnal->get());
  }

}
