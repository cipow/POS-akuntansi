<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePenjualan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang__transaksi_penjualan', function (Blueprint $table) {
          $table->increments('id');
          $table->unsignedInteger('transaksi_id');
          $table->unsignedInteger('pelanggan_id')->nullable();
          $table->timestamp('tanggal')->nullable();
          $table->string('nofaktur', 100);
          $table->string('jenis_pembayaran', 100);
          $table->timestamp('tanggal_tempo')->nullable();
          $table->integer('jumlah');
          $table->integer('harga');
          $table->bigInteger('total');
          $table->bigInteger('total_piutang');
          // $table->timestamps();

          $table->foreign('transaksi_id')->references('id')->on('barang__transaksi')->onDelete('cascade')->onUpdate('cascade');
          $table->foreign('pelanggan_id')->references('id')->on('barang__pelanggan')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang__transaksi_penjualan');
    }
}
