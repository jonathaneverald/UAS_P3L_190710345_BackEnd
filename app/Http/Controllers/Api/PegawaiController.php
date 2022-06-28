<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class PegawaiController extends Controller
{
    public function login(Request $request){
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validate->fails())
            return response (['message' => $validate->errors()], 400);
        
        $pegawai = Pegawai::with('role')->where('email',$loginData['email'])->get();

        if($pegawai->isEmpty())
            return response (['message' => 'Email tidak terdaftar'], 401);
        else{
            $passwordTemp = pegawai::where('email',$loginData['email'])->first();
            $password = $passwordTemp->password;

            if(!Hash::check($loginData['password'], $password))
                return response (['message' => 'Password salah!'], 401);
            else{
                return response ([
                   'message' => 'Authenticated',
                   'pegawai' => $pegawai
                ]);
            }
        }   
    }

    public function logout(){
        return response (['message' => 'Logout berhasil']);
    }

    public function index(){
        $pegawai = Pegawai::with('role')->get(); // mengambil semua data pegawai

        if(count($pegawai) > 0){
            return response([
                'message' => 'Retrieve All pegawai Success',
                'data' => $pegawai
            ], 200);
        } // return data semua pegawai dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data pegawai kosong
    }

    public function show($id){
        $pegawai = Pegawai::with('role')->find($id); // mencari data pegawai berdasarkan id

        if(!is_null($pegawai)){
            return response([
                'message' => 'Retrieve Pegawai Success',
                'data' => $pegawai
            ], 200);
        } // return data pegawai yang ditemukan dalam bentuk json

        return response([
            'message' => 'Pegawai Not Found',
            'data' => null
        ], 404); // return message saat data pegawai tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_role' => 'required|numeric',
            'nama_pegawai' => 'required',
            'alamat' => 'required',
            'tanggal_lahir' => 'required|date_format:y-m-d',
            'jenis_kelamin' => 'required',
            'email' => 'required|email|unique:pegawai',
            'no_telp' => 'required|regex:/^(08)[0-9]{11}$/',
            'pas_foto' => 'nullable|file',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

        $file_name = time().'.'.$request->pas_foto->extension();
        $request->pas_foto->move(public_path('pas_foto'),$file_name);
        $path = $file_name;
        $storeData['pas_foto'] = $path;

        $year = Carbon::now()->format('y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');

        $pegawai = Pegawai::create($storeData);

        $year = Carbon::createFromFormat('y-m-d', $pegawai->tanggal_lahir)->format('y');
        $month = Carbon::createFromFormat('y-m-d', $pegawai->tanggal_lahir)->format('m');
        $day = Carbon::createFromFormat('y-m-d', $pegawai->tanggal_lahir)->format('d');

        $pegawai->password = bcrypt($year.$month.$day);
        $pegawai->save();

        return response([
            'message' => 'Add Pegawai Success',
            'data' => $pegawai
        ], 200); // return data pegawai baru dalam bentuk json
    }

    public function destroy($id){
        $pegawai = Pegawai::with('role')->find($id); // mencari data pegawai berdasarkan id

        if(is_null($pegawai)){
            return response([
                'message' => 'Pegawai Not Found',
                'data' => null
            ], 404); // return message saat data pegawai tidak ditemukan
        }

        if(File::exists(public_path('pas_foto/'.$pegawai->pas_foto))){
            File::delete(public_path('pas_foto/'.$pegawai->pas_foto));
            if($pegawai->delete()){
                return response([
                    'message' => 'Delete Pegawai Success',
                    'data' => $pegawai
                ], 200);
            } // return message saat berhasil menghapus data pegawai
    
            return response([
                'message' => 'Delete Pegawai Failed',
                'data' => null,
            ], 400); // return message saat gagal menghapus data pegawai
        }
        else{
            return response([
                'message' => 'Delete Pas Foto Pegawai Failed',
            ], 400);
        }
        
    }

    public function update(Request $request, $id){
        $pegawai = Pegawai::find($id);
        if (is_null($pegawai)){
            return response([
                'message' => 'Pegawai Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'id_role' => 'required|numeric',
            'nama_pegawai' => 'required',
            'alamat' => 'required',
            'tanggal_lahir' => 'required|date_format:y-m-d',
            'jenis_kelamin' => 'required',
            'email' => ['required', "email", Rule::unique('pegawai')->ignore($pegawai)],
            'no_telp' => 'required|regex:/^(08)[0-9]{11}$/',
            'pas_foto' => 'nullable|file',
            
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);
        
        $pas_foto = $pegawai->pas_foto;

        if(File::exists(public_path('pas_foto/'.$pas_foto)))
            File::delete(public_path('pas_foto/'.$pas_foto));  
        else{
            return response([
                'message' => 'Update File Pegawai Failed',
                'data' => null,
            ], 400); // return message saat gagal update data pegawai
        }    

        $file_name = time().'.'.$request->pas_foto->extension();
        $request->pas_foto->move(public_path('pas_foto'),$file_name);
        $path = $file_name;
        $updateData['pas_foto'] = $path;
        
        $pegawai->id_role = $updateData['id_role'];
        $pegawai->nama_pegawai = $updateData['nama_pegawai'];
        $pegawai->alamat = $updateData['alamat'];
        $pegawai->tanggal_lahir = $updateData['tanggal_lahir'];
        $pegawai->jenis_kelamin = $updateData['jenis_kelamin'];
        $pegawai->email = $updateData['email'];
        $pegawai->no_telp = $updateData['no_telp'];
        $pegawai->pas_foto = $updateData['pas_foto'];
        $pegawai->password = bcrypt($updateData['password']);

        if($pegawai->save()) {
            return response([
                'message' => 'Update Pegawai Success',
                'data' => $pegawai
            ], 200);
        }

        return response([
            'message' => 'Update Pegawai Failed',
            'data' => null,
        ], 400);
    }

}
