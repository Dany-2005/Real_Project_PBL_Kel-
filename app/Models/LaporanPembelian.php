<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPembelian extends Model
{
    protected $table = 'laporan_pembelian';

    protected $fillable = [
        'id_pembelian',
        'tanggal',
        'total',
    ];
}