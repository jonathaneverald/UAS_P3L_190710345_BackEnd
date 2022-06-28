<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'driver';
    public $timestamps = false;
    protected $primaryKey = 'id_driver';

    protected $fillable = [
        'id_driver', 'tampil_id_driver', 'format_id_driver', 'nama_driver', 'alamat_driver', 'tanggal_lahir', 'jenis_kelamin', 'email', 
        'no_telp', 'bahasa', 'pas_foto', 'sim', 'surat_bebas_napza', 'surat_kesehatan_jiwa', 'surat_kesehatan_jasmani', 
        'skck', 'tarif_driver', 'status_driver', 'status_dokumen', 'password',
    ];

    public function getTanggalLahirAttribute() {
        if(!is_null($this->attributes['tanggal_lahir'])) {
            return Carbon::parse($this->attributes['tanggal_lahir'])->format('y-m-d');
        }
    } 
}
