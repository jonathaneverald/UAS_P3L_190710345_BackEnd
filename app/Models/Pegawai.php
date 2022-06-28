<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';
    public $timestamps = false;
    protected $primaryKey = 'id_pegawai';

    protected $fillable = [
        'id_pegawai', 'id_role', 'nama_pegawai', 'alamat', 'tanggal_lahir', 
        'jenis_kelamin', 'email', 'no_telp', 'pas_foto', 'password',
    ];
    
    public function getTanggalLahirAttribute() {
        if(!is_null($this->attributes['tanggal_lahir'])) {
            return Carbon::parse($this->attributes['tanggal_lahir'])->format('y-m-d');
        }
    } 

    public function role(){
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function jadwal(){
        return $this->belongsToMany(Jadwal_Pegawai::class, 'detail_jadwal_pegawai', 'id_pegawai', 'id_jadwal');
    }
}
