<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(){
        $role = Role::all(); // mengambil semua data role

        if(count($role) > 0){
            return response([
                'message' => 'Retrieve All Role Success',
                'data' => $role
            ], 200);
        } // return data semua role dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data role kosong
    }

    public function show($id){
        $role = Role::find($id); // mencari data role berdasarkan id

        if(!is_null($role)){
            return response([
                'message' => 'Retrieve Role Success',
                'data' => $role
            ], 200);
        } // return data role yang ditemukan dalam bentuk json

        return response([
            'message' => 'Role Not Found',
            'data' => null
        ], 404); // return message saat data role tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'jabatan' => 'required',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

        $role = Role::create($storeData);
        $role->save();

        return response([
            'message' => 'Add Role Success',
            'data' => $role
        ], 200); // return data role baru dalam bentuk json
    }

    public function destroy($id){
        $role = Role::find($id); // mencari data role berdasarkan id

        if(is_null($role)){
            return response([
                'message' => 'Role Not Found',
                'data' => null
            ], 404); // return message saat data role tidak ditemukan
        }

        if($role->delete()){
            return response([
                'message' => 'Delete Role Success',
                'data' => $role
            ], 200);
        } // return message saat berhasil menghapus data role
    
        return response([
            'message' => 'Delete Role Failed',
            'data' => null,
        ], 400); // return message saat gagal menghapus data role
    }

    public function update(Request $request, $id){
        $role = Role::find($id);
        if (is_null($role)){
            return response([
                'message' => 'Role Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'jabatan' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $role->jabatan = $updateData['jabatan'];

        if($role->save()) {
            return response([
                'message' => 'Update Role Success',
                'data' => $role
            ], 200);
        }

        return response([
            'message' => 'Update Role Failed',
            'data' => null,
        ], 400);
    }
}
