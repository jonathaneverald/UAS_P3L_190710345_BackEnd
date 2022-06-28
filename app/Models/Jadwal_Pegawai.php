<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal_Pegawai extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pegawai';
    public $timestamps = false;
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_jadwal', 'hari', 'shift',
    ];

    public function pegawai(){
        return $this->belongsToMany(Pegawai::class, 'detail_jadwal_pegawai', 'id_pegawai', 'id_jadwal');
    }
}
