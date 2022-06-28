<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promo;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    public function index(){
        $promo = Promo::all(); // mengambil semua data promo

        if(count($promo) > 0){
            return response([
                'message' => 'Retrieve All Promo Success',
                'data' => $promo
            ], 200);
        } // return data semua promo dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data promo kosong
    }

    public function all() {
        $promo = Promo::where("status_promo", 1)->get(); // mengambil semua data promo

        if (count($promo) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'promo' => $promo
            ], 200);
        } // return data semua promo dalam bentuk json

        return response([
            'message' => 'Empty',
            'promo' => null
        ], 400); // return message data promo kosong
    }

    public function show($id){
        $promo = Promo::find($id); // mencari data promo berdasarkan id

        if(!is_null($promo)){
            return response([
                'message' => 'Retrieve Promo Success',
                'data' => $promo
            ], 200);
        } // return data promo yang ditemukan dalam bentuk json

        return response([
            'message' => 'Promo Not Found',
            'data' => null
        ], 404); // return message saat data promo tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'kode_promo' => 'required',
            'jenis_promo' => 'required',
            'keterangan_promo' => 'required',
            'potongan_promo' => 'required|numeric|min:1|max:100',
            'status_promo' => 'required|boolean',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

        $promo = Promo::create($storeData);
        $promo->save();

        return response([
            'message' => 'Add Promo Success',
            'data' => $promo
        ], 200); // return data promo baru dalam bentuk json
    }

    public function destroy($id){
        $promo = Promo::find($id); // mencari data promo berdasarkan id

        if(is_null($promo)){
            return response([
                'message' => 'Promo Not Found',
                'data' => null
            ], 404); // return message saat data promo tidak ditemukan
        }

        if($promo->delete()){
            return response([
                'message' => 'Delete Promo Success',
                'data' => $promo
            ], 200);
        } // return message saat berhasil menghapus data promo
    
        return response([
            'message' => 'Delete Promo Failed',
            'data' => null,
        ], 400); // return message saat gagal menghapus data promo
    }

    public function update(Request $request, $id){
        $promo = Promo::find($id);
        if (is_null($promo)){
            return response([
                'message' => 'Promo Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'kode_promo' => 'required',
            'jenis_promo' => 'required',
            'keterangan_promo' => 'required',
            'potongan_promo' => 'required|numeric|min:1|max:100',
            'status_promo' => 'required|boolean',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $promo->kode_promo = $updateData['kode_promo'];
        $promo->jenis_promo = $updateData['jenis_promo'];
        $promo->keterangan_promo = $updateData['keterangan_promo'];
        $promo->potongan_promo = $updateData['potongan_promo'];
        $promo->status_promo = $updateData['status_promo'];

        if($promo->save()) {
            return response([
                'message' => 'Update Promo Success',
                'data' => $promo
            ], 200);
        }

        return response([
            'message' => 'Update Promo Failed',
            'data' => null,
        ], 400);
    }
}
