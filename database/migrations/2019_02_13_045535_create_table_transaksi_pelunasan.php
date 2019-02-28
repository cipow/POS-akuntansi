<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTransaksiPelunasan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi__pelunasan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('transaksi_id');
            $table->timestamp('tanggal')->nullable();
            $table->bigInteger('nilai');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('kredit')->default(0);
            $table->bigInteger('saldo')->default(0);
            $table->text('keterangan')->nullable();
            // $table->timestamps();

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
        Schema::dropIfExists('transaksi__pelunasan');
    }
}
