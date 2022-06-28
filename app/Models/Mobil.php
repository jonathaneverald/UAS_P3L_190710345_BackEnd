<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Mobil extends Model
{
    use HasFactory;

    protected $table = 'mobil';
    public $timestamps = false;
    protected $primaryKey = 'id_mobil_sewa';

    protected $fillable = [
        'id_mobil_sewa', 'id_pemilik_mobil_sewa', 'nama_mobil_sewa', 'tipe_mobil_sewa', 'jenis_transmisi', 
        'jenis_bahan_bakar', 'warna_mobil_sewa', 'volume_bagasi_mobil', 'fasilitas_mobil', 'harga_sewa_mobil',
        'kapasitas', 'plat_nomor', 'no_stnk', 'kategori_aset', 'tgl_terakhir_servis', 'status_mobil', 'foto_mobil', 
        'periode_mulai_sewa', 'periode_akhir_sewa',
    ];

    public function pemilik_mobil(){
        return $this->belongsTo(Pemilik_Mobil::class, 'id_pemilik_mobil_sewa');
    }

    public function getTglTerakhirServisAttribute() {
        if(!is_null($this->attributes['tgl_terakhir_servis'])) {
            return Carbon::parse($this->attributes['tgl_terakhir_servis'])->format('y-m-d');
        }
    }

    public function getPeriodeMulaiSewaAttribute() {
        if(!is_null($this->attributes['periode_mulai_sewa'])) {
            return Carbon::parse($this->attributes['periode_mulai_sewa'])->format('y-m-d');
        }
    }

    public function getPeriodeAkhirSewaAttribute() {
        if(!is_null($this->attributes['periode_akhir_sewa'])) {
            return Carbon::parse($this->attributes['periode_akhir_sewa'])->format('y-m-d');
        }
    }

}
