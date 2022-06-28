<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    public $timestamps = false;
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_transaksi', 'tampil_id_transaksi', 'format_id_transaksi', 'id_customer', 'id_mobil_sewa', 
        'id_driver', 'id_promo', 'id_pegawai', 
        'tanggal_transaksi_sewa_mobil', 'tanggal_mulai_sewa_mobil', 'tanggal_selesai_sewa_mobil', 'tanggal_pengembalian_mobil',
        'jenis_penyewaan_mobil', 'metode_pembayaran', 'denda_penyewaan', 'bukti_pembayaran', 'rating_driver', 'rating_perusahaan',
        'status_transaksi', 'total_transaksi'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function pegawai(){
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

    public function driver(){
        return $this->belongsTo(Driver::class, 'id_driver');
    }

    public function mobil(){
        return $this->belongsTo(Mobil::class, 'id_mobil_sewa');
    }

    public function promo(){
        return $this->belongsTo(Promo::class, 'id_promo');
    }

    public function getTanggalTransaksiSewaMobilAttribute() {
        if(!is_null($this->attributes['tanggal_transaksi_sewa_mobil'])) {
            return Carbon::parse($this->attributes['tanggal_transaksi_sewa_mobil'])->format('y-m-d H:i');
        }
    }

    public function getTanggalMulaiSewaMobilAttribute() {
        if(!is_null($this->attributes['tanggal_mulai_sewa_mobil'])) {
            return Carbon::parse($this->attributes['tanggal_mulai_sewa_mobil'])->format('y-m-d H:i');
        }
    }

    public function getTanggalSelesaiSewaMobilAttribute() {
        if(!is_null($this->attributes['tanggal_selesai_sewa_mobil'])) {
            return Carbon::parse($this->attributes['tanggal_selesai_sewa_mobil'])->format('y-m-d H:i');
        }
    }

    public function getTanggalPengembalianMobilAttribute() {
        if(!is_null($this->attributes['tanggal_pengembalian_mobil'])) {
            return Carbon::parse($this->attributes['tanggal_pengembalian_mobil'])->format('y-m-d H:i');
        }
    }
}
