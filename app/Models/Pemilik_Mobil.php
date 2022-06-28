<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pemilik_Mobil extends Model
{
    use HasFactory;

    protected $table = 'pemilik_mobil';
    public $timestamps = false;
    protected $primaryKey = 'id_pemilik_mobil_sewa';

    protected $fillable = [
        'id_pemilik_mobil_sewa', 'nama_pemilik_mobil_sewa', 'no_ktp', 'no_hp',
        'alamat',
    ];
}
