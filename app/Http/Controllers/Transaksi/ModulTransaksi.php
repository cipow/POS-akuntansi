<?php

namespace App\Http\Controllers\Transaksi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Transaksi\Transaksi;
use Carbon\Carbon;

class ModulTransaksi extends Controller {

  public static function buatTransaksi($jenis, Request $req, $tanggal) {
    $jenisTransaksi = ($jenis == 'B') ? 'pembelian':'penjualan';

    $transaksi = Transaksi::create([
      'jenis' => $jenisTransaksi,
      'tanggal' => $tanggal,
      'tanggal_tempo' => $req->tanggal_tempo,
    ]);

    $transaksi->update(['nofaktur' => $jenis.'-'.$tanggal->year.$tanggal->month.$tanggal->day.'-'.$transaksi->id]);
    return $transaksi;
  }

  public static function totalTransaksiBarang($jenis, $transaksi_id, $barangs) {
    $total = 0;
    foreach ($barangs as $barang) {
      $barang = (object) $barang;
      $dataBarang = Barang::find($barang->id);
      if ($dataBarang) {
        if ($jenis == 'J' && $dataBarang->stok <= 0) continue;
        if ($jenis == 'B') {
          if ($dataBarang->harga_rata == 0) $harga_rata = $barang->harga;
          else $harga_rata = ceil((($dataBarang->stok * $dataBarang->harga_rata) + ($barang->jumlah * $barang->harga)) / ($dataBarang->stok + $barang->jumlah));
        }
        else $harga_rata = $dataBarang->harga_rata;

        $total_harga_barang = $barang->harga * $barang->jumlah;
        $saldo_kg = ($jenis == 'B') ? $dataBarang->stok + $barang->jumlah: $dataBarang->stok - $barang->jumlah;
        $saldo_rp = $saldo_kg * $harga_rata;
        $total = $total + $total_harga_barang;

        $dataBarang->update(['stok' => $saldo_kg, 'harga_rata' => $harga_rata]);
        $dataBarang->barangTransaksi()->create([
          'transaksi_id' => $transaksi_id,
          'kg' => $barang->jumlah,
          'harga' => $barang->harga,
          'total' => $total_harga_barang,
          'saldo_kg' => $saldo_kg,
          'harga_rata' => $harga_rata,
          'saldo_rp' => $saldo_rp
        ]);
      }
    }

    return $total;
  }

}
