<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Detail_Jadwal_Pegawai extends Model
{
    use HasFactory;

    protected $table = 'detail_jadwal_pegawai';
    public $timestamps = false;
    

    protected $fillable = [
        'id_pegawai', 'id_jadwal',
    ];
}
