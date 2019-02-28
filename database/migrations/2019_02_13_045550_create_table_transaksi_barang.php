<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTransaksiBarang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang_transaksi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('transaksi_id');
            $table->integer('kg');
            $table->integer('harga');
            $table->bigInteger('total');
            $table->integer('saldo_kg');
            $table->integer('harga_rata');
            $table->bigInteger('saldo_rp');
            // $table->timestamps();

            $table->foreign('barang_id')->references('id')->on('barang')->onUpdate('cascade');
            $table->foreign('transaksi_id')->references('id')->on('transaksi')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang_transaksi');
    }
}
