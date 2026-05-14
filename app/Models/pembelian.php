<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';

    protected $fillable = [
        'tanggal',
        'id_suplier',
        'total',
        'keterangan'
    ];

    public function detail()
    {
        return $this->hasMany(DetailPembelian::class, 'id_pembelian');
    }

    public function suplier()
    {
        return $this->belongsTo(Suplier::class, 'id_suplier');
    }
}