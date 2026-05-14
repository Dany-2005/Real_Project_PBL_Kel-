<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->bigIncrements('id_detail_pembelian');

            $table->unsignedBigInteger('id_pembelian');
            $table->unsignedBigInteger('id_produk');

            $table->integer('jumlah');
            $table->enum('tipe_stok', ['gudang', 'toko'])->default('gudang');
            $table->integer('harga_beli');
            $table->integer('subtotal');

            $table->timestamps();

            $table->foreign('id_pembelian')
                ->references('id_pembelian')
                ->on('pembelian')
                ->onDelete('cascade');

            $table->foreign('id_produk')
                ->references('id_produk')
                ->on('produk')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian');
    }
};