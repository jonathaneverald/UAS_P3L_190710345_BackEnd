<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mobil;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class MobilController extends Controller
{
    public function index(){
        $mobil = Mobil::with('pemilik_mobil')->get(); // mengambil semua data mobil

        if(count($mobil) > 0){
            return response([
                'message' => 'Retrieve All Mobil Success',
                'data' => $mobil
            ], 200);
        } // return data semua mobil dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data mobil kosong
    }

    public function all() {
        $mobil = Mobil::where("status_mobil", "Tersedia")->get(); // mengambil semua data mobil

        if(count($mobil) > 0){
            return response([
                'message' => 'Retrieve All Mobil Success',
                'data' => $mobil
            ], 200);
        } // return data semua mobil dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data mobil kosong
    }

    public function show($id){
        $mobil = Mobil::with('pemilik_mobil')->find($id); // mencari data mobil berdasarkan id

        if(!is_null($mobil)){
            return response([
                'message' => 'Retrieve Mobil Success',
                'data' => $mobil
            ], 200);
        } // return data mobil yang ditemukan dalam bentuk json

        return response([
            'message' => 'Mobil Not Found',
            'data' => null
        ], 404); // return message saat data mobil tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_pemilik_mobil_sewa ' => 'nullable',
            'nama_mobil_sewa' => 'required',
            'tipe_mobil_sewa' => 'required',
            'jenis_transmisi' => 'required',
            'jenis_bahan_bakar' => 'required',
            'warna_mobil_sewa' => 'required',
            'volume_bagasi_mobil' => 'required',
            'fasilitas_mobil' => 'required',
            'harga_sewa_mobil' => 'required|numeric',
            'kapasitas' => 'required|numeric',
            'plat_nomor' => 'required',
            'no_stnk' => 'required',
            'kategori_aset' => 'required|boolean',
            'tgl_terakhir_servis' => 'required|date_format:y-m-d',
            'status_mobil' => 'required',
            'foto_mobil' => 'nullable|file',
            'periode_mulai_sewa' => 'nullable|date_format:y-m-d',
            'periode_akhir_sewa' => 'nullable|date_format:y-m-d',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

    
        if($storeData['kategori_aset'] == 1){
            $validate = Validator::make($storeData, [
                'periode_mulai_sewa' => 'required|date_format:y-m-d',
                'periode_akhir_sewa' => 'required|date_format:y-m-d',
            ]);
            $periode_mulai_sewa = Carbon::createFromFormat('y-m-d', $storeData['periode_mulai_sewa']);
            $periode_akhir_sewa = Carbon::createFromFormat('y-m-d', $storeData['periode_akhir_sewa']);
            if($periode_akhir_sewa<$periode_mulai_sewa || $periode_mulai_sewa > Carbon::now()){
                return response ([
                    'message' =>'Inputan Periode Salah'
                ], 400);
            }
        }

        $file_name = time().'.'.$request->foto_mobil->extension();
        $request->foto_mobil->move(public_path('foto_mobil'),$file_name);
        $path = $file_name;
        $storeData['foto_mobil'] = $path;
        

        $mobil = Mobil::create($storeData);
        $mobil->save();

        return response([
            'message' => 'Add Mobil Success',
            'data' => $mobil
        ], 200); // return data mobil baru dalam bentuk json
    }

    public function destroy($id){
        $mobil = Mobil::with('pemilik_mobil')->find($id); // mencari data mobil berdasarkan id

        if(is_null($mobil)){
            return response([
                'message' => 'Mobil Not Found',
                'data' => null
            ], 404); // return message saat data mobil tidak ditemukan
        }

        if(File::exists(public_path('foto_mobil/'.$mobil->foto_mobil))){
            File::delete(public_path('foto_mobil/'.$mobil->foto_mobil));
            if($mobil->delete()){
                return response([
                    'message' => 'Delete Mobil Success',
                    'data' => $mobil
                ], 200);
            } // return message saat berhasil menghapus data mobil
    
            return response([
                'message' => 'Delete Mobil Failed',
                'data' => null,
            ], 400); // return message saat gagal menghapus data mobil
        }
        else{
            return response([
                'message' => 'Delete Pas Foto Mobil Failed',
            ], 400);
        }
        
    }

    public function update(Request $request, $id){
        $mobil = Mobil::find($id);
        if (is_null($mobil)){
            return response([
                'message' => 'Mobil Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'id_pemilik_mobil_sewa ' => 'nullable|numeric',
            'nama_mobil_sewa' => 'required',
            'tipe_mobil_sewa' => 'required',
            'jenis_transmisi' => 'required',
            'jenis_bahan_bakar' => 'required',
            'warna_mobil_sewa' => 'required',
            'volume_bagasi_mobil' => 'required',
            'fasilitas_mobil' => 'required',
            'harga_sewa_mobil' => 'required|numeric',
            'kapasitas' => 'required|numeric',
            'plat_nomor' => 'required',
            'no_stnk' => 'required',
            'kategori_aset' => 'required|boolean',
            'tgl_terakhir_servis' => 'required|date_format:y-m-d',
            'status_mobil' => 'required',
            'foto_mobil' => 'nullable|file',
            'periode_mulai_sewa' => 'nullable|date_format:y-m-d',
            'periode_akhir_sewa' => 'nullable|date_format:y-m-d',
            
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);
        
        if($updateData['kategori_aset'] == 1){
            $validate = Validator::make($updateData, [
                'id_pemilik_mobil_sewa' => 'required',
                'periode_mulai_sewa' => 'required|date_format:y-m-d',
                'periode_akhir_sewa' => 'required|date_format:y-m-d',
            ]);
        }
        $foto_mobil = $mobil->foto_mobil;

        if(File::exists(public_path('foto_mobil/'.$foto_mobil)))
            File::delete(public_path('foto_mobil/'.$foto_mobil));  
        else{
            return response([
                'message' => 'Update File Mobil Failed',
                'data' => null,
            ], 400); // return message saat gagal update data mobil
        }    

        $file_name = time().'.'.$request->foto_mobil->extension();
        $request->foto_mobil->move(public_path('foto_mobil'),$file_name);
        $path = $file_name;
        $updateData['foto_mobil'] = $path;
        
        $mobil->id_pemilik_mobil_sewa = $updateData['id_pemilik_mobil_sewa'];
        $mobil->nama_mobil_sewa = $updateData['nama_mobil_sewa'];
        $mobil->tipe_mobil_sewa = $updateData['tipe_mobil_sewa'];
        $mobil->jenis_transmisi = $updateData['jenis_transmisi'];
        $mobil->jenis_bahan_bakar = $updateData['jenis_bahan_bakar'];
        $mobil->warna_mobil_sewa = $updateData['warna_mobil_sewa'];
        $mobil->volume_bagasi_mobil = $updateData['volume_bagasi_mobil'];
        $mobil->fasilitas_mobil = $updateData['fasilitas_mobil'];
        $mobil->harga_sewa_mobil = $updateData['harga_sewa_mobil'];
        $mobil->kapasitas = $updateData['kapasitas'];
        $mobil->plat_nomor = $updateData['plat_nomor'];
        $mobil->no_stnk = $updateData['no_stnk'];
        $mobil->kategori_aset = $updateData['kategori_aset'];
        $mobil->tgl_terakhir_servis = $updateData['tgl_terakhir_servis'];
        $mobil->status_mobil = $updateData['status_mobil'];
        $mobil->foto_mobil = $updateData['foto_mobil'];
        $mobil->periode_mulai_sewa = $updateData['periode_mulai_sewa'];
        $mobil->periode_akhir_sewa = $updateData['periode_akhir_sewa'];
        

        if($mobil->save()) {
            return response([
                'message' => 'Update Mobil Success',
                'data' => $mobil
            ], 200);
        }

        return response([
            'message' => 'Update Mobil Failed',
            'data' => null,
        ], 400);
    }

    public function kontrakMobil(){
        $mobil = Mobil::with('pemilik_mobil')->whereBetween('periode_akhir_sewa', [Carbon::now(), Carbon::now()->addDays(30)])->get();
        
        if(count($mobil)>0){
            return response([
                'message' => 'Retrieve All Mobil Success',
                'data' => $mobil
            ], 200);
        } // return data semua mobil dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data mobil kosong
    }
}
