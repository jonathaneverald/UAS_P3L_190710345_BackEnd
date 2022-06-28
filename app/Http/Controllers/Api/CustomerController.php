<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function login(Request $request){
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validate->fails())
            return response (['message' => $validate->errors()], 400);
        
        $customer = customer::where('email',$loginData['email'])->get();

        if($customer->isEmpty())
            return response (['message' => 'Email tidak terdaftar'], 401);
        else{
            $passwordTemp = customer::where('email',$loginData['email'])->first();
            $password = $passwordTemp->password;

            if(!Hash::check($loginData['password'], $password))
                return response (['message' => 'Password salah!'], 401);
            else{
                return response ([
                   'message' => 'Authenticated',
                   'customer' => $customer
                ]);
            }
        }   
    }

    public function logout(){
        return response (['message' => 'Logout berhasil']);
    }

    public function checkDocument(Request $request, $id){
        $customer = Customer::find($id); // mencari data customer berdasarkan id

        if(!is_null($customer)){
            return response([
                'message' => 'Retrieve Customer Success',
                'data' => $customer
            ], 200);
        } // return data customer yang ditemukan dalam bentuk json

        return response([
            'message' => 'Customer Not Found',
            'data' => null
        ], 404); // return message saat data customer tidak ditemukan

    }

    public function index(){
        $customer = Customer::all(); // mengambil semua data customer

        if(count($customer) > 0){
            return response([
                'message' => 'Retrieve All Customer Success',
                'data' => $customer
            ], 200);
        } // return data semua customer dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data customer kosong
    }

    public function show($id){
        $customer = Customer::find($id); // mencari data customer berdasarkan id

        if(!is_null($customer)){
            return response([
                'message' => 'Retrieve Customer Success',
                'customer' => $customer
            ], 200);
        } // return data customer yang ditemukan dalam bentuk json

        return response([
            'message' => 'Customer Not Found',
            'customer' => null
        ], 404); // return message saat data customer tidak ditemukan
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_customer' => 'required',
            'alamat_customer' => 'required',
            'tanggal_lahir' => 'required|date_format:y-m-d',
            'jenis_kelamin' => 'required',
            'email' => 'required|email|unique:customer',
            'no_telp' => 'required|regex:/^(08)[0-9]{11}$/',
            'tanda_pengenal' => 'required',
            'foto_sim' => 'nullable|file',
            'dokumen_persyaratan' => 'nullable|file',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

        $file_name = time().'.'.$request->foto_sim->extension();
        $request->foto_sim->move(public_path('foto_sim'),$file_name);
        $path = $file_name;
        $storeData['foto_sim'] = $path;

        $file_name1 = time().'.'.$request->dokumen_persyaratan->extension();
        $request->dokumen_persyaratan->move(public_path('dokumen_persyaratan'),$file_name1);
        $path1 = $file_name1;
        $storeData['dokumen_persyaratan'] = $path1;

        $year = Carbon::now()->format('y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');

        $format = 'CUS'.$year.$month.$day.'-';
        $storeData['format_id_customer'] = $format;

        $customer = Customer::create($storeData);
        $customer->tampil_id_customer = str_pad($customer->id_customer,3,'0',STR_PAD_LEFT);
        $customer ->format_id_customer = $format.$customer->tampil_id_customer;

        $year = Carbon::createFromFormat('y-m-d', $customer->tanggal_lahir)->format('y');
        $month = Carbon::createFromFormat('y-m-d', $customer->tanggal_lahir)->format('m');
        $day = Carbon::createFromFormat('y-m-d', $customer->tanggal_lahir)->format('d');

        $customer->password = bcrypt($year.$month.$day);
        $customer->status_dokumen = "Dalam Proses";
        $customer->save();

        return response([
            'message' => 'Add Customer Success',
            'data' => $customer
        ], 200); // return data customer baru dalam bentuk json
    }

    public function destroy($id){
        $customer = Customer::find($id); // mencari data customer berdasarkan id

        if(is_null($customer)){
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404); // return message saat data customer tidak ditemukan
        }

        if(File::exists(public_path('foto_sim/'.$customer->foto_sim)) && public_path('dokumen_persyaratan/'.$customer->dokumen_persyaratan)) {
            File::delete(public_path('foto_sim/'.$customer->foto_sim));
            File::delete(public_path('dokumen_persyaratan/'.$customer->dokumen_persyaratan));
            
            if($customer->delete()){
                return response([
                    'message' => 'Delete Customer Success',
                    'data' => $customer
                ], 200);
            } // return message saat berhasil menghapus data customer
    
            return response([
                'message' => 'Delete Customer Failed',
                'data' => null,
            ], 400); // return message saat gagal menghapus data customer
        }
        else{
            return response([
                'message' => 'Delete Foto Sim & Dokumen Peryaratan Failed',
            ], 400);
        }
        
    }

    public function update(Request $request, $id){
        $customer = Customer::find($id);
        if (is_null($customer)){
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'nama_customer' => 'required',
            'alamat_customer' => 'required',
            'tanggal_lahir' => 'required|date_format:y-m-d',
            'jenis_kelamin' => 'required',
            'email' => ['required', "email", Rule::unique('customer')->ignore($customer)],
            'no_telp' => 'required',
            'tanda_pengenal' => 'required',
            'password' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $customer->nama_customer = $updateData['nama_customer'];
        $customer->alamat_customer = $updateData['alamat_customer'];
        $customer->tanggal_lahir = $updateData['tanggal_lahir'];
        $customer->jenis_kelamin = $updateData['jenis_kelamin'];
        $customer->email = $updateData['email'];
        $customer->no_telp = $updateData['no_telp'];
        $customer->tanda_pengenal = $updateData['tanda_pengenal'];
        $customer->status_dokumen = $updateData['status_dokumen'];
        $customer->password = bcrypt($updateData['password']);
        $customer->tampil_id_customer = str_pad($customer->id_customer,3,'0',STR_PAD_LEFT);

        if($customer->save()) {
            return response([
                'message' => 'Update Customer Success',
                'data' => $customer
            ], 200);
        }

        return response([
            'message' => 'Update Customer Failed',
            'data' => null,
        ], 400);
    }

    public function editCustomerMobile(Request $request, $id) {
        $customer = Customer::find($id);
        if(is_null($customer)){
            return response([
                'message' => 'Customer Not Found',
                'customer' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'alamat_customer' => 'required',
            'email' => ['required', "email", Rule::unique('customer')->ignore($customer)],
            'no_telp' => 'required|regex:/^(08)[0-9]{11}$/',
            'password' => 'nullable'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $customer->alamat_customer = $updateData['alamat_customer'];
        $customer->email = $updateData['email'];
        $customer->no_telp = $updateData['no_telp'];
        if($updateData['password'] != null) 
            $customer->password = bcrypt($updateData['password']);

        if ($customer->save()) {
            return response([
                'message' => 'Update Customer Success',
                'customer' => $customer
        ], 200);
        } // return data customer baru dalam bentuk json

        return response([
            'message' => 'Update Customer Failed',
            'customer' => null,
        ], 400);
    }
}