<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jadwal_Pegawai;
use Illuminate\Support\Facades\Validator;

class Jadwal_PegawaiController extends Controller
{
    public function index(){
        $jadwal_pegawai = Jadwal_Pegawai::all(); // mengambil semua data Jadwal Pegawai

        if(count($jadwal_pegawai) > 0){
            return response([
                'message' => 'Retrieve All Jadwal Pegawai Success',
                'data' => $jadwal_pegawai
            ], 200);
        } // return data semua jadwal pegawai dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data jadwal pegawai kosong
    }

    public function show($id){
        $jadwal_pegawai = Jadwal_Pegawai::find($id); // mencari data jadwal pegawai berdasarkan id

        if(!is_null($jadwal_pegawai)){
            return response([
                'message' => 'Retrieve Jadwal Pegawai Success',
                'data' => $jadwal_pegawai
            ], 200);
        } // return data jadwal pegawai yang ditemukan dalam bentuk json

        return response([
            'message' => 'Jadwal Pegawai Not Found',
            'data' => null
        ], 404); // return message saat data jadwal pegawai tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'hari' => 'required',
            'shift' => 'required',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

          
        $check = jadwal_pegawai::all()->where('hari', $storeData['hari'])->where('shift', $storeData['shift']);
        if(count($check) > 0){
            return response ([
                'message' => 'Jadwal sudah terdaftar',
                'check' => $check
            ], 400);
        }
        else{
            $jadwal_pegawai = Jadwal_Pegawai::create($storeData);
            $jadwal_pegawai->save();

            return response([
                'message' => 'Add Jadwal Pegawai Success',
                'data' => $jadwal_pegawai
            ], 200); // return data jadwal pegawai baru dalam bentuk json
        } 
    }

    public function destroy($id){
        $jadwal_pegawai = Jadwal_Pegawai::find($id); // mencari data jadwal pegawai berdasarkan id

        if(is_null($jadwal_pegawai)){
            return response([
                'message' => 'Jadwal Pegawai Not Found',
                'data' => null
            ], 404); // return message saat data jadwal pegawai tidak ditemukan
        }

        if($jadwal_pegawai->delete()){
            return response([
                'message' => 'Delete Jadwal Pegawai Success',
                'data' => $jadwal_pegawai
            ], 200);
        } // return message saat berhasil menghapus data jadwal pegawai
    
        return response([
            'message' => 'Delete Jadwal Pegawai Failed',
            'data' => null,
        ], 400); // return message saat gagal menghapus data jadwal pegawai
    }

    public function update(Request $request, $id){
        $jadwal_pegawai = Jadwal_Pegawai::find($id);
        if (is_null($jadwal_pegawai)){
            return response([
                'message' => 'Jadwal Pegawai Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'hari' => 'required',
            'shift' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $check = strval(jadwal_pegawai::where('hari', '=', $updateData['hari'])->where('shift', '=', $updateData['shift'])->value('id_jadwal'));
        if($check != null){
            if($id != $check){
                return response ([
                    'message' => 'Jadwal sudah terdaftar',
                    'jadwal' => null
                ], 400);
            }
            return response ([
                'message' => 'Update Jadwal Success',
                'data' => $jadwal_pegawai
            ], 200);
        }

        $jadwal_pegawai->hari = $updateData['hari'];
        $jadwal_pegawai->shift = $updateData['shift'];

        if($jadwal_pegawai->save()) {
            return response([
                'message' => 'Update Jadwal Pegawai Success',
                'data' => $jadwal_pegawai
            ], 200);
        }

        return response([
            'message' => 'Update Jadwal Pegawai Failed',
            'data' => null,
        ], 400);
    }
}
