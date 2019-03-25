<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLaporanKas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laporan_kas', function (Blueprint $table) {
          $table->increments('id');
          $table->unsignedInteger('user_id');
          $table->timestamp('tanggal')->nullable();
          $table->date('tanggal_laporan');
          $table->bigInteger('pelunasan_piutang');
          $table->bigInteger('pelunasan_hutang');
          $table->bigInteger('beban_angkut');
          $table->bigInteger('beban_gaji');
          $table->bigInteger('beban_operasional');
          $table->bigInteger('beban_pajak');
          $table->bigInteger('total_operasi');
          $table->bigInteger('asset_tanah');
          $table->bigInteger('asset_perlengkapan');
          $table->bigInteger('asset_kendaraan');
          $table->bigInteger('asset_bangunan');
          $table->bigInteger('asset_peralatan');
          $table->bigInteger('total_investasi');
          $table->bigInteger('total_prive');
          $table->bigInteger('kenaikan_saldo');
          $table->bigInteger('saldo_awal_bulan');
          $table->bigInteger('saldo_akhir_bulan');

          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laporan_kas');
    }
}
