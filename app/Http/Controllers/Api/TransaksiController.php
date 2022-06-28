<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Customer;
use App\Models\Mobil;
use App\Models\Promo;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use PDF;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function index(){
        $transaksi = Transaksi::with('customer', 'pegawai', 'driver', 'mobil', 'promo')->get(); // mengambil semua data pegawai

        if(count($transaksi) > 0){
            return response([
                'message' => 'Retrieve All Transaksi Success',
                'transaksi' => $transaksi
            ], 200);
        } // return data semua transaksi dalam bentuk json

        return response([
            'message' => 'Empty',
            'transaksi' => null
        ], 400); // return message data transaksi kosong
    }

    public function show($id){
        $transaksi = Transaksi::with('customer', 'pegawai', 'driver', 'mobil', 'promo')->where("id_customer", $id)->get();; // mencari data pegawai berdasarkan id

        if(!is_null($transaksi)){
            return response([
                'message' => 'Retrieve Transaksi Success',
                'transaksi' => $transaksi
            ], 200);
        } // return data transaksi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Transaksi Not Found',
            'transaksi' => null
        ], 404); // return message saat data transaksi tidak ditemukan
    }

    public function showDriver($id) {
        $transaksi = Transaksi::with('customer', 'mobil', 'driver', 'promo', 'pegawai')->where("id_driver", $id)->get(); // mencari data transaksi berdasarkan id

        if(!$transaksi->isEmpty()) {
            return response([
                'message' => 'Retrieve Transaksi Success',
                'transaksi' => $transaksi
            ], 200);
        } // return data transaksi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Transaksi Not Found',
            'transaksi' => null
        ], 404); // return message saat data transaksi tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_customer' => 'required|numeric',
            'id_mobil_sewa' => 'required|numeric',
            'id_driver' => 'nullable|numeric',
            'id_promo' => 'nullable|numeric',
            'tanggal_mulai_sewa_mobil' => 'required|date_format:y-m-d H:i',
            'tanggal_selesai_sewa_mobil' => 'required|date_format:y-m-d H:i',
            'jenis_penyewaan_mobil' => 'required',
            'metode_pembayaran' => 'required',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

        $tanggal_mulai_sewa_mobil = Carbon::createFromFormat('y-m-d H:i', $storeData['tanggal_mulai_sewa_mobil']);
        $tanggal_selesai_sewa_mobil = Carbon::createFromFormat('y-m-d H:i', $storeData['tanggal_selesai_sewa_mobil']);

        if($tanggal_selesai_sewa_mobil < $tanggal_mulai_sewa_mobil || $tanggal_mulai_sewa_mobil < Carbon::now()) {
            return response(['message' => 'Inputan Tanggal Tidak Sesuai'], 400);
        }

        $transaksi = transaksi::where("id_customer", $storeData['id_customer'])->where(function($q) {
                $q->where("status_transaksi", "!=", "Berhasil");
            })->get();
        
        if(!$transaksi->isEmpty()) {
            return response([
                'message' => 'Tidak Bisa Menambah Transaksi',
            ], 400);
        }

        $year = Carbon::now()->format('y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');

        $format = 'TRN'.$year.$month.$day.'-';
        $storeData['format_id_transaksi'] = $format;
        $transaksi = Transaksi::create($storeData);
        $transaksi->tampil_id_transaksi = str_pad($transaksi->id_transaksi,3,'0',STR_PAD_LEFT);
        $transaksi ->format_id_transaksi = $format.$transaksi->tampil_id_transaksi;
        $transaksi->tanggal_transaksi_sewa_mobil = Carbon::now();

        $customer = Customer::find($storeData['id_customer']);
        if($customer->status_dokumen == "Dalam Proses" || $customer->status_dokumen == "Tidak Lengkap") {
            if($customer->status_dokumen == "Dalam Proses") {
                $transaksi->status_transaksi = "Transaksi Belum Diverifikasi";
            } else {
                $transaksi->status_transaksi = "Transaksi Gagal Diverifikasi";
            }
        } else if($customer->status_dokumen == "Lengkap") {
            $transaksi->status_transaksi = "Transaksi Sedang Berjalan";
        }

        $transaksi->save();

        return response([
            'message' => 'Add Transaksi Success',
            'transaksi' => $transaksi
        ], 200); // return data transaksi baru dalam bentuk json
    }

    public function destroy($id){
        $transaksi = Transaksi::with('customer', 'pegawai', 'driver', 'mobil', 'promo')->find($id); // mencari data pegawai berdasarkan id

        if(is_null($transaksi)){
            return response([
                'message' => 'Transaksi Not Found',
                'transaksi' => null
            ], 404); // return message saat data transaksi tidak ditemukan
        }

        if(File::exists(public_path('bukti_pembayaran/'.$transaksi->bukti_pembayaran))){
            File::delete(public_path('bukti_pembayaran/'.$transaksi->bukti_pembayaran));
            if($transaksi->delete()){
                return response([
                    'message' => 'Delete Transaksi Success',
                    'transaksi' => $transaksi
                ], 200);
            } // return message saat berhasil menghapus data transaksi
    
            return response([
                'message' => 'Delete Transaksi Failed',
                'transaksi' => null,
            ], 400); // return message saat gagal menghapus data transaksi
        }
        else{
            return response([
                'message' => 'Delete Bukti Pembayaran Transaksi Failed',
            ], 400);
        } 
    }

    public function update(Request $request, $id){
        $transaksi = Transaksi::find($id);
        if (is_null($transaksi)){
            return response([
                'message' => 'Transaksi Not Found',
                'transaksi' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'id_pegawai' => 'required|numeric',
            'tanggal_pengembalian_mobil' => 'required|date_format:y-m-d H:i',
            'status_transaksi' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);
        
        $transaksi->id_pegawai = $updateData['id_pegawai'];
        $transaksi->tanggal_pengembalian_mobil = $updateData['tanggal_pengembalian_mobil'];
        $transaksi->status_transaksi = $updateData['status_transaksi'];
        
        $mobil = Mobil::find($transaksi->id_mobil_sewa);
        $tanggal_mulai_sewa_mobil = Carbon::createFromFormat('y-m-d H:i', $transaksi->tanggal_mulai_sewa_mobil);
        $tanggal_selesai_sewa_mobil = Carbon::createFromFormat('y-m-d H:i', $transaksi->tanggal_selesai_sewa_mobil);
        $tanggal_pengembalian_mobil = Carbon::createFromFormat('y-m-d H:i', $transaksi->tanggal_pengembalian_mobil);

        if($tanggal_pengembalian_mobil<$tanggal_selesai_sewa_mobil) {
            return response(['message' => 'Inputan Tanggal Tidak Sesuai'], 400);
        }

        $temp = explode(" ", $transaksi->tanggal_selesai_sewa_mobil);
        $temp2 = explode(" ", $transaksi->tanggal_pengembalian_mobil);
        
        $selisih = $tanggal_mulai_sewa_mobil->diffInDays($tanggal_selesai_sewa_mobil);
        $total = $selisih*$mobil->harga_sewa_mobil;

        if($transaksi->id_driver != null) {
            $total = $total + ($transaksi->driver->tarif_driver * $selisih);
        }

        if($transaksi->id_promo != null) {
            $promo = Promo::find($transaksi->id_promo);
            $diskon = ($promo->potongan_promo)/100.0;
            $total = $total-($total*$diskon);
        }

        $tanggal_selesai_sewa_mobil = Carbon::createFromFormat('y-m-d', $temp[0]);
        $tanggal_pengembalian_mobil = Carbon::createFromFormat('y-m-d', $temp2[0]);

        if($temp[0] == $temp2[0]) {
            $time = Carbon::createFromFormat('H:i', $temp[1]);
            $time2 = Carbon::createFromFormat('H:i', $temp2[1]);
            $selisih = $time->floatDiffInHours($time2);
            if($selisih>3.0) {
                $denda_penyewaan = $mobil->harga_sewa_mobil;
                $transaksi->denda_penyewaan = $denda_penyewaan;
                $total = $total + $denda_penyewaan;
            } else $transaksi->denda_penyewaan = 0;
        } else {
            $selisih = $tanggal_selesai_sewa_mobil->diffInDays($tanggal_pengembalian_mobil);
            $denda_penyewaan = $selisih*$mobil->harga_sewa_mobil;
            $transaksi->denda_penyewaan = $denda_penyewaan;
            $total = $total + $denda_penyewaan;
        }

        $transaksi->total_transaksi = $total;

        if ($transaksi->save()) {
            return response([
                'message' => 'Update Transaksi Success',
                'transaksi' => $transaksi
        ], 200);
        } // return data transaksi baru dalam bentuk json

        return response([
            'message' => 'Update Transaksi Failed',
            'transaksi' => null,
        ], 400);
    }

    public function updateTransaksiCustomer(Request $request, $id) {
        $transaksi = Transaksi::find($id);
        if(is_null($transaksi)){
            return response([
                'message' => 'Transaksi Not Found',
                'transaksi' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'id_mobil_sewa' => 'required',
            'id_promo' => 'nullable',
            'id_driver' => 'nullable',
            'tanggal_mulai_sewa_mobil' => 'required|date_format:y-m-d H:i',
            'tanggal_selesai_sewa_mobil' => 'required|date_format:y-m-d H:i',
            'metode_pembayaran' => 'required',
            'jenis_penyewaan_mobil' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $tanggal_mulai_sewa_mobil = Carbon::createFromFormat('y-m-d H:i', $updateData['tanggal_mulai_sewa_mobil']);
        $tanggal_selesai_sewa_mobil = Carbon::createFromFormat('y-m-d H:i', $updateData['tanggal_selesai_sewa_mobil']);

        if($tanggal_selesai_sewa_mobil < $tanggal_mulai_sewa_mobil || $tanggal_mulai_sewa_mobil < Carbon::now()) {
            return response(['message' => 'Inputan Tanggal Tidak Sesuai'], 400);
        }

        if($updateData['jenis_penyewaan_mobil'] == "Peminjaman Mobil")
            $transaksi->id_driver = null;
        if($updateData['id_driver'] != null) 
        $transaksi->id_driver = $updateData['id_driver'];

        $transaksi->id_mobil_sewa = $updateData['id_mobil_sewa'];
        $transaksi->id_promo = $updateData['id_promo'];
        $transaksi->tanggal_mulai_sewa_mobil = $updateData['tanggal_mulai_sewa_mobil'];
        $transaksi->tanggal_selesai_sewa_mobil = $updateData['tanggal_selesai_sewa_mobil'];
        $transaksi->metode_pembayaran = $updateData['metode_pembayaran'];
        $transaksi->jenis_penyewaan_mobil = $updateData['jenis_penyewaan_mobil'];

        if ($transaksi->save()) {
            return response([
                'message' => 'Update Transaksi Success',
                'transaksi' => $transaksi
        ], 200);
        } // return data transaksi baru dalam bentuk json

        return response([
            'message' => 'Update Transaksi Failed',
            'transaksi' => null,
        ], 400);
    }

    public function pembayaran(Request $request, $id) {
        $transaksi = Transaksi::find($id);
        if(is_null($transaksi)) {
            return response([
                'message' => 'Transaksi Not Found',
                'transaksi' => null
            ], 404);
        } 

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'bukti_pembayaran' => 'nullable|file',
            'rating_driver' => 'required|numeric',
            'rating_perusahaan' => 'required|numeric',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        if($updateData["bukti_pembayaran"] != null) {
            $file_name = time().'.'.$request->bukti_pembayaran->extension();
            $request->bukti_pembayaran->move(public_path('bukti_pembayaran'),$file_name);
            $path = "$file_name";
            $updateData['bukti_pembayaran'] = $path;
            $transaksi->bukti_pembayaran = $updateData['bukti_pembayaran'];
        }

        $transaksi->rating_driver = $updateData['rating_driver'];
        $transaksi->rating_perusahaan = $updateData['rating_perusahaan'];

        if ($transaksi->save()) {
            return response([
                'message' => 'Pembayaran Berhasil',
                'transaksi' => $transaksi
        ], 200);
        } // return data transaksi baru dalam bentuk json

        return response([
            'message' => 'Pembayaran Gagal',
            'transaksi' => null,
        ], 400);
    }

    public function cetak_nota($id) {
        $transaksi = Transaksi::with('customer', 'mobil', 'driver', 'promo', 'pegawai')->where("id_transaksi",$id)->get();

        $pdf = PDF::loadview('nota_pdf',['transaksi'=>$transaksi]);
    	return $pdf->stream("Nota Transaksi Atma Rental.pdf");
    }

    public function laporanTopDriver(Request $request) {
        $laporan = $request->all();
        $validate = Validator::make($laporan, [
            'year' => 'required',
            'month' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $laporan = DB::table("transaksi")
            ->join('driver', 'driver.id_driver', '=', 'transaksi.id_driver')
            ->select(DB::raw("driver.format_id_driver, driver.nama_driver, COUNT(transaksi.id_transaksi) as jumlah_peminjaman"))->where("status_transaksi", "Berhasil")
            ->whereYear('tanggal_transaksi_sewa_mobil', $laporan['year'])->whereMonth('tanggal_transaksi_sewa_mobil', $laporan['month'])
            ->orderBy(DB::raw('COUNT(id_transaksi)'), 'DESC')->groupBy('transaksi.id_driver')
            ->take(5)->get();

        if(!$laporan->isEmpty()) {
            return response([
                'message' => 'Retrieve Laporan Transaksi Success',
                'laporan' => $laporan
            ], 200);
        } // return data transaksi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Laporan Transaksi Not Found',
            'laporan' => null
        ], 404); // return message saat data transaksi tidak ditemukan
    }

    public function laporanPerformaDriver(Request $request) {
        $laporan = $request->all();
        $validate = Validator::make($laporan, [
            'year' => 'required',
            'month' => 'required',
        ]);
        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $laporan = DB::table("transaksi")
            ->join('driver', 'driver.id_driver', '=', 'transaksi.id_driver')
            ->select(DB::raw("driver.format_id_driver, driver.nama_driver, COUNT(transaksi.id_transaksi) as jumlah_peminjaman, SUM(transaksi.rating_driver)/COUNT(transaksi.id_driver) as rerata_rating"))->where("status_transaksi", "Berhasil")
            ->whereYear('tanggal_transaksi_sewa_mobil', $laporan['year'])->whereMonth('tanggal_transaksi_sewa_mobil', $laporan['month'])
            ->orderBy(DB::raw('COUNT(id_transaksi)'), 'DESC')->groupBy('transaksi.id_driver')
            ->take(5)->get();

        if(!$laporan->isEmpty()) {
            return response([
                'message' => 'Retrieve Laporan Transaksi Success',
                'laporan' => $laporan
            ], 200);
        } // return data transaksi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Laporan Transaksi Not Found',
            'laporan' => null
        ], 404); // return message saat data transaksi tidak ditemukan
    }

    public function laporanTopCustomer(Request $request) {
        $laporan = $request->all();
        $validate = Validator::make($laporan, [
            'year' => 'required',
            'month' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $laporan = DB::table("transaksi")
            ->join('customer', 'customer.id_customer', '=', 'transaksi.id_customer')
            ->select(DB::raw("customer.nama_customer, COUNT(transaksi.id_transaksi) as jumlah_peminjaman"))->where("status_transaksi", "Berhasil")
            ->whereYear('tanggal_transaksi_sewa_mobil', $laporan['year'])->whereMonth('tanggal_transaksi_sewa_mobil', $laporan['month'])
            ->orderBy(DB::raw('COUNT(id_transaksi)'), 'DESC')->groupBy('transaksi.id_customer')
            ->take(5)->get();

        if(!$laporan->isEmpty()) {
            return response([
                'message' => 'Retrieve Laporan Transaksi Success',
                'laporan' => $laporan
            ], 200);
        } // return data transaksi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Laporan Transaksi Not Found',
            'laporan' => null
        ], 404); // return message saat data transaksi tidak ditemukan
    }

    public function laporanDetailPendapatan(Request $request) {
        $laporan = $request->all();
        $validate = Validator::make($laporan, [
            'year' => 'required',
            'month' => 'required',
        ]);
        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $laporan = DB::table("transaksi")
            ->join('customer', 'customer.id_customer', '=', 'transaksi.id_customer')
            ->join('mobil', 'mobil.id_mobil_sewa', '=', 'transaksi.id_mobil_sewa')
            ->select(DB::raw("customer.nama_customer, mobil.nama_mobil_sewa, jenis_penyewaan_mobil, COUNT(transaksi.id_customer) as jumlah_peminjaman, SUM(total_transaksi) as pendapatan"))->where("status_transaksi", "Berhasil")
            ->whereYear('tanggal_transaksi_sewa_mobil', $laporan['year'])->whereMonth('tanggal_transaksi_sewa_mobil', $laporan['month'])
            ->groupBy('transaksi.id_customer', 'jenis_penyewaan_mobil')->get();

        if(!$laporan->isEmpty()) {
            return response([
                'message' => 'Retrieve Laporan Transaksi Success',
                'laporan' => $laporan
            ], 200);
        } // return data transaksi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Laporan Transaksi Not Found',
            'laporan' => null
        ], 404); // return message saat data transaksi tidak ditemukan
    }

    public function laporanSewaMobil(Request $request) {
        $laporan = $request->all();
        $validate = Validator::make($laporan, [
            'year' => 'required',
            'month' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $laporan = DB::table("transaksi")
        ->join('mobil', 'mobil.id_mobil_sewa', '=', 'transaksi.id_mobil_sewa')
        ->select(DB::raw("mobil.tipe_mobil_sewa, mobil.nama_mobil_sewa, COUNT(transaksi.id_mobil_sewa) as jumlah_peminjaman, SUM(total_transaksi) as pendapatan"))->where("status_transaksi", "Berhasil")
        ->whereYear('tanggal_transaksi_sewa_mobil', $laporan['year'])->whereMonth('tanggal_transaksi_sewa_mobil', $laporan['month'])
        ->orderBy(DB::raw('SUM(total_transaksi)'))->groupBy('transaksi.id_mobil_sewa')->get();

        if(!$laporan->isEmpty()) {
            return response([
                'message' => 'Retrieve Laporan Transaksi Success',
                'laporan' => $laporan
            ], 200);
        } // return data transaksi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Laporan Transaksi Not Found',
            'laporan' => null
        ], 404); // return message saat data transaksi tidak ditemukan
    }
}
