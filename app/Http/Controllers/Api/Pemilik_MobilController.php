<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemilik_Mobil;
use Illuminate\Support\Facades\Validator;



class Pemilik_MobilController extends Controller
{
    public function index(){
        $pemilik_mobil = Pemilik_Mobil::all(); // mengambil semua data pemilik mobil

        if(count($pemilik_mobil) > 0){
            return response([
                'message' => 'Retrieve All Pemilik Mobil Success',
                'data' => $pemilik_mobil
            ], 200);
        } // return data semua pemilik mobil dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data pemilik mobil kosong
    }

    public function show($id){
        $pemilik_mobil = Pemilik_Mobil::find($id); // mencari data pemilik mobil berdasarkan id

        if(!is_null($pemilik_mobil)){
            return response([
                'message' => 'Retrieve Pemilik Mobil Success',
                'data' => $pemilik_mobil
            ], 200);
        } // return data pemilik mobil yang ditemukan dalam bentuk json

        return response([
            'message' => 'Pemilik Mobil Not Found',
            'data' => null
        ], 404); // return message saat data pemilik mobil tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_pemilik_mobil_sewa' => 'required',
            'no_ktp' => 'required|numeric|digits:16',
            'no_hp' => 'required|regex:/^(08)[0-9]{11}$/',
            'alamat' => 'required',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

        $pemilik_mobil = Pemilik_Mobil::create($storeData);
        $pemilik_mobil->save();

        return response([
            'message' => 'Add Pemilik Mobil Success',
            'data' => $pemilik_mobil
        ], 200); // return data pemilik mobil baru dalam bentuk json
    }

    public function destroy($id){
        $pemilik_mobil = Pemilik_Mobil::find($id); // mencari data pemilik mobil berdasarkan id

        if(is_null($pemilik_mobil)){
            return response([
                'message' => 'Pemilik Mobil Not Found',
                'data' => null
            ], 404); // return message saat data pemilik mobil tidak ditemukan
        }

        if($pemilik_mobil->delete()){
            return response([
                'message' => 'Delete Pemilik Mobil Success',
                'data' => $pemilik_mobil
            ], 200);
        } // return message saat berhasil menghapus data pemilik mobil
    
        return response([
            'message' => 'Delete Pemilik Mobil Failed',
            'data' => null,
        ], 400); // return message saat gagal menghapus data pemilik mobil
    }

    public function update(Request $request, $id){
        $pemilik_mobil = Pemilik_Mobil::find($id);
        if (is_null($pemilik_mobil)){
            return response([
                'message' => 'Pemilik Mobil Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'nama_pemilik_mobil_sewa' => 'required',
            'no_ktp' => 'required|digits:16',
            'no_hp' => 'required|regex:/^(08)[0-9]{11}$/',
            'alamat' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $pemilik_mobil->nama_pemilik_mobil_sewa = $updateData['nama_pemilik_mobil_sewa'];
        $pemilik_mobil->no_ktp = $updateData['no_ktp'];
        $pemilik_mobil->no_hp = $updateData['no_hp'];
        $pemilik_mobil->alamat = $updateData['alamat'];

        if($pemilik_mobil->save()) {
            return response([
                'message' => 'Update Pemilik Mobil Success',
                'data' => $pemilik_mobil
            ], 200);
        }

        return response([
            'message' => 'Update Pemilik Mobil Failed',
            'data' => null,
        ], 400);
    }
}
