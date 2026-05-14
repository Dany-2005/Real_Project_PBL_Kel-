<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suplier extends Model
{
    protected $table = 'suplier'; // ← hapus s
    protected $primaryKey = 'id_suplier';

    protected $fillable = [
        'nama_suplier',
        'no_hp',
        'alamat',
    ];
}