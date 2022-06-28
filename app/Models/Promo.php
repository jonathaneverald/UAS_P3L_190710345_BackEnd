<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $table = 'promo';
    public $timestamps = false;
    protected $primaryKey = 'id_promo';

    protected $fillable = [
        'id_promo', 'kode_promo', 'jenis_promo',
        'keterangan_promo', 'potongan_promo', 'status_promo',
    ];
}
