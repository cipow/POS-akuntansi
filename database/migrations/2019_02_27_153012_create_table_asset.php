<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAsset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users__asset', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->enum('kategori', ['tanah', 'perlengkapan', 'kendaraan', 'bangunan', 'peralatan']);
            $table->timestamp('tanggal')->nullable();
            $table->string('nama', 100);
            $table->integer('harga_beli');
            $table->integer('umur_tahun')->default(0);
            $table->integer('nilai_penyusutan')->default(0);
            $table->integer('nilai_sekarang');
            $table->timestamp('masa_berakhir')->nullable();
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
        Schema::dropIfExists('users__asset');
    }
}
