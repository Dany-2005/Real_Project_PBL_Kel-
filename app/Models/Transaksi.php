<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pelanggan;





class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

protected $fillable = [
    'tanggal',
    'id_user',
    'id_pelanggan',
    'subtotal',
    'total_diskon',
    'total',
    'bayar',
    'kembalian',
    'metode_pembayaran',
    'catatan',
];

    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
        
    }
public function produk()
{
    return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
}
public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class, 'id_pelanggan');

}
public function kasir()
{
    return $this->belongsTo(\App\Models\User::class, 'id_user');
}

}