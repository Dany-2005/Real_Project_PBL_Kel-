<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'id_kategori',
        'harga_satuan',
        'harga_grosir',
        'minimal_grosir',
        'stok_gudang',
        'stok_toko',
        'isi_per_dus',
        'satuan',
    ];
    
public function diskon()
{
    return $this->belongsToMany(Diskon::class, 'diskon_produk', 'id_produk', 'id_diskon');
}
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}