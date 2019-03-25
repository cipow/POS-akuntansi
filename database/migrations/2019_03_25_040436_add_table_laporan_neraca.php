<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableLaporanNeraca extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laporan_neraca', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->timestamp('tanggal')->nullable();
            $table->date('tanggal_laporan');
            $table->bigInteger('kas');
            $table->bigInteger('modal');
            $table->bigInteger('piutang');
            $table->bigInteger('hutang');
            $table->bigInteger('persediaan_akhir');
            $table->bigInteger('tanah');
            $table->bigInteger('perlengkapan');
            $table->bigInteger('bangunan');
            $table->bigInteger('depresiasi_bangunan');
            $table->bigInteger('peralatan');
            $table->bigInteger('depresiasi_peralatan');
            $table->bigInteger('kendaraan');
            $table->bigInteger('depresiasi_kendaraan');
            $table->bigInteger('aktiva');
            $table->bigInteger('passiva');

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
        Schema::dropIfExists('laporan_neraca');
    }
}
