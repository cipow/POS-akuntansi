<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTransaksi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pemasok_id')->nullable();
            $table->unsignedInteger('pelanggan_id')->nullable();
            $table->enum('jenis',['pembelian', 'penjualan']);
            $table->string('nofaktur', 100);
            $table->timestamp('tanggal')->nullable();
            $table->timestamp('tanggal_tempo')->nullable();
            $table->bigInteger('total');
            $table->bigInteger('ph_utang');
            // $table->timestamps();

            $table->foreign('pemasok_id')->references('id')->on('pemasok')->onUpdate('cascade');
            $table->foreign('pelanggan_id')->references('id')->on('pelanggan')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
}
