<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePelunasanHutang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang__pelunasan_hutang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('pembelian_id');
            $table->timestamp('tanggal')->nullable();
            $table->bigInteger('nilai');
            $table->text('keterangan')->nullable();
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->bigInteger('saldo')->default(0);
            // $table->timestamps();

            $table->foreign('pembelian_id')->references('id')->on('barang__transaksi_pembelian')->onDelete('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang__pelunasan_hutang');
    }
}
