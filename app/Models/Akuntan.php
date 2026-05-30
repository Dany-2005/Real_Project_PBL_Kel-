<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id_akun';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'pos_saldo',
        'pos_laporan',
        'kategori_neraca',
        'kategori_laba_rugi',
        'flag_transaksi_offline',
        'flag_transaksi_online',
        'flag_kas_offline',
        'flag_kas_online',
    ];

    protected $casts = [
        'flag_transaksi_offline' => 'boolean',
        'flag_transaksi_online'  => 'boolean',
        'flag_kas_offline'       => 'boolean',
        'flag_kas_online'        => 'boolean',
    ];

    // Relasi ke transaksi
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_akun');
    }
}