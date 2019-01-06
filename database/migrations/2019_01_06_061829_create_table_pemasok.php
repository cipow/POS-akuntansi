<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePemasok extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang__pemasok', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('bank', 100)->nullable();
            $table->string('no_rekening', 100)->nullable();
            $table->string('an_rekening', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang__pemasok');
    }
}
