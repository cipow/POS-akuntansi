<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableKeuangan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users__keuangan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('transaksi_id')->nullable();
            $table->unsignedBigInteger('pelunasan_id')->nullable();
            $table->unsignedInteger('asset_id')->nullable();
            $table->unsignedInteger('lp_bulan_id')->nullable();
            $table->timestamp('tanggal')->nullable();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->bigInteger('nilai');
            $table->string('kategori', 100);
            $table->bigInteger('saldo_kas');
            $table->text('keterangan')->nullable();
            // $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('transaksi_id')->references('id')->on('transaksi')->onUpdate('cascade');
            $table->foreign('pelunasan_id')->references('id')->on('transaksi__pelunasan')->onUpdate('cascade');
            $table->foreign('asset_id')->references('id')->on('users__asset')->onUpdate('cascade');
            $table->foreign('lp_bulan_id')->references('id')->on('laporan_bulanan')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users__keuangan');
    }
}
