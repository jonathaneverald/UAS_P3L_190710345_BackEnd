<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Detail_Jadwal_Pegawai;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Validator;


class Detail_Jadwal_PegawaiController extends Controller
{
    public function index(){
        $role = "Manager";
        $detail_jadwal_pegawai = Pegawai::with('jadwal')->with('role')->whereHas('role', function($r) use($role) {
            $r->where('jabatan', '!=', $role);
        })->get();
        if(count($detail_jadwal_pegawai) > 0){
            return response([
                'message' => 'Retrieve All Detail Jadwal Pegawai Success',
                'detail' => $detail_jadwal_pegawai
            ], 200);
        } // return data semua detail jadwal pegawai dalam bentuk json

        return response([
            'message' => 'Empty',
            'detail' => null
        ], 400); // return message data detail jadwal pegawai kosong
    }

    public function show($id){
        $detail_jadwal_pegawai = Pegawai::where('id_pegawai', '=', $id)->with('jadwal')->first(); // mencari data jadwal pegawai berdasarkan id

        if(!is_null($detail_jadwal_pegawai)){
            return response([
                'message' => 'Retrieve Detail Jadwal Pegawai Success',
                'detail' => $detail_jadwal_pegawai
            ], 200);
        } // return data detail jadwal pegawai yang ditemukan dalam bentuk json

        return response([
            'message' => 'Detail Jadwal Pegawai Not Found',
            'detail' => null
        ], 404); // return message saat data detail jadwal pegawai tidak ditemukan
    }

    public function store(Request $request, $id){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_jadwal' => 'required',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input
        
        $count = detail_jadwal_pegawai::select('id_pegawai')->where('id_pegawai', $id)->count();
        if($count==6){
            return response([
                'message' => 'Batas mengambil shift perminggu adalah 6',
            ], 400);
        }

        $detail_jadwal_pegawai = Pegawai::find($id);
        $detail_jadwal_pegawai->jadwal()->attach($storeData['id_jadwal']);

        return response([
            'message' => 'Add Jadwal Pegawai Success',
            'detail_jadwal_pegawai' => $detail_jadwal_pegawai->with('jadwal')->get()
        ], 200); // return data detail jadwal pegawai baru dalam bentuk json
    }

    public function destroy($idPegawai, $idJadwal){
        $detail_jadwal_pegawai = Pegawai::where('id_pegawai', '=', $idPegawai)->with('jadwal')->first();    
        //$detail_jadwal_pegawai = Pegawai::where('id_pegawai', '=', $id)->with('jadwal')->get(); // mencari data detail jadwal pegawai berdasarkan id ->where('id_pegawai',$id)

        if(is_null($detail_jadwal_pegawai)){
            return response([
                'message' => 'Detail Jadwal Pegawai Not Found',
                'detail' => null
            ], 404); // return message saat data detail jadwal pegawai tidak ditemukan
        }

        if($detail_jadwal_pegawai->jadwal()->detach($idJadwal)){
            $detail_jadwal_pegawai->refresh();
            return response([
                'message' => 'Delete Detail Jadwal Pegawai Success',
                'detail' => $detail_jadwal_pegawai
            ], 200);
        } // return message saat berhasil menghapus data detail jadwal pegawai
    
        return response([
            'message' => 'Delete Detail Jadwal Pegawai Failed',
            'detail' => null,
        ], 400); // return message saat gagal menghapus data detail jadwal pegawai
    }

    public function update(Request $request, $idPegawai){
        $detail_jadwal_pegawai = Pegawai::where('id_pegawai', '=', $idPegawai)->with('jadwal')->first(); // mencari data detail jadwal pegawai berdasarkan id
        if (is_null($detail_jadwal_pegawai)){
            return response([
                'message' => 'Detail Jadwal Pegawai Not Found',
                'detail' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'id_jadwalLama' => 'required',
            'id_jadwalBaru' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);
        if($detail_jadwal_pegawai->jadwal()->updateExistingPivot($updateData['id_jadwalLama'], ['id_jadwal'=> $updateData['id_jadwalBaru']])){
            $detail_jadwal_pegawai->refresh();
            return response([
                'message' => 'Update Detail Jadwal Pegawai Success',
                'detail' => $detail_jadwal_pegawai
            ], 200);
        }

        return response([
            'message' => 'Update Detail Jadwal Pegawai Failed',
            'detail' => null,
        ], 400);
    }
}
