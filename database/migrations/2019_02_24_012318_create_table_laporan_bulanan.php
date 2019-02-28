<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLaporanBulanan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laporan_bulanan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->timestamp('tanggal')->nullable();
            $table->date('tanggal_laporan');
            $table->bigInteger('penjualan');
            $table->bigInteger('pembelian');
            $table->bigInteger('persediaan_awal');
            $table->bigInteger('persediaan_akhir');
            $table->bigInteger('beban_penjualan');
            $table->bigInteger('beban_pembelian');
            $table->bigInteger('beban_gaji');
            $table->bigInteger('beban_operasional');
            $table->bigInteger('beban_pajak');
            $table->bigInteger('laba_kotor');
            $table->bigInteger('laba_bersih');
            // $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laporan_bulanan');
    }
}
