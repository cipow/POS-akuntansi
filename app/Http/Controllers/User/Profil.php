<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Profil extends Controller {

  private $user;

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

  public function riwayatKeuangan() {
    return $this->response->data($this->user->keuangan()->orderBy('tanggal', 'desc')->get());
  }

  public function detailKeuangan($id) {
    return $this->response->data($this->user->keuangan()->with(['transaksi', 'pelunasan', 'asset', 'lpBulan'])->find($id));
  }
}
