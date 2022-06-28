<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';
    public $timestamps = false;
    protected $primaryKey = 'id_customer';

    protected $fillable = [
        'id_customer', 'tampil_id_customer', 'format_id_customer', 'nama_customer', 'alamat_customer', 'tanggal_lahir', 
        'jenis_kelamin', 'email', 'no_telp', 'tanda_pengenal', 'foto_sim', 'dokumen_persyaratan', 'password',
    ];

    public function getTanggalLahirAttribute() {
        if(!is_null($this->attributes['tanggal_lahir'])) {
            return Carbon::parse($this->attributes['tanggal_lahir'])->format('y-m-d');
        }
    } 
}
