<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('diskon', function (Blueprint $table) {
        $table->bigIncrements('id_diskon');
        $table->string('nama_diskon');
        $table->integer('besar_diskon'); 
        $table->integer('minimal_beli')->default(1);
        $table->date('mulai_tgl');
        $table->date('selesai_tgl');
        $table->boolean('is_aktif')->default(true);
        
        // Tambahkan id_pelanggan
        $table->unsignedBigInteger('id_pelanggan')->nullable(); 
        
        // Tambahkan onDelete('set null') agar aman jika pelanggan dihapus
        $table->foreign('id_pelanggan')
              ->references('id_pelanggan')
              ->on('pelanggan')
              ->onDelete('set null');
              
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('diskon');
    }
};