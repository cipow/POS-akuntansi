<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBarangTransaksi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang__transaksi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('barang_id');
            $table->timestamp('tanggal')->nullable();
            $table->string('nofaktur_pembelian', 100)->nullable();
            $table->string('nofaktur_penjualan', 100)->nullable();
            $table->integer('masuk_kg')->default(0);
            $table->integer('harga_beli')->default(0);
            $table->integer('total_pembelian')->default(0);
            $table->integer('keluar_kg')->default(0);
            $table->integer('harga_jual')->default(0);
            $table->integer('total_penjualan')->default(0);
            $table->integer('saldo_kg')->default(0);
            $table->integer('harga_rata')->default(0);

            $table->foreign('barang_id')->references('id')->on('barang')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang__transaksi');
    }
}
