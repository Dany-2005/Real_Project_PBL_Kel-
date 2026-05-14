<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->bigIncrements('id_pembelian');
            $table->date('tanggal');
            $table->unsignedBigInteger('id_suplier')->nullable();
            $table->integer('total')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_suplier')
                ->references('id_suplier')
                ->on('suplier')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};