<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'jenis',
        'tanggal',
        'id_user',
        'id_pelanggan',
        'id_suplier',
        'subtotal',
        'total_diskon',
        'total',
        'bayar',
        'kembalian',
        'metode_pembayaran',
        'keterangan',
        'catatan',
    ];

    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function kasir()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user');
    }

    public function suplier()
    {
        return $this->belongsTo(Suplier::class, 'id_suplier');
    }

    // Scope filter jenis
    public function scopePenjualan($query)
    {
        return $query->where('jenis', 'penjualan');
    }

    public function scopePembelian($query)
    {
        return $query->where('jenis', 'pembelian');
    }
}