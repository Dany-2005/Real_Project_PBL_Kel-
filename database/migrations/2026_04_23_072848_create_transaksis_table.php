<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
  Schema::create('transaksi', function (Blueprint $table) {
    $table->bigIncrements('id_transaksi');
    $table->date('tanggal');
    $table->unsignedBigInteger('id_user');
    $table->unsignedBigInteger('id_pelanggan')->nullable(); // nullable karena bisa umum
    $table->integer('subtotal')->default(0);               // sebelum diskon
    $table->integer('total_diskon')->default(0);           // total diskon
    $table->integer('total')->default(0);                  // setelah diskon
    $table->integer('bayar')->default(0);                  // uang yang dibayar
    $table->integer('kembalian')->default(0);              // kembalian
    $table->string('metode_pembayaran')->nullable();
    $table->text('catatan')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
