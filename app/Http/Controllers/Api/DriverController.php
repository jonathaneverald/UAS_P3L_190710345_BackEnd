<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Transaksi;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function login(Request $request){
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validate->fails())
            return response (['message' => $validate->errors()], 400);
        
            $driver = Driver::where('email',$loginData['email'])->get();

        if($driver->isEmpty())
            return response (['message' => 'Email tidak terdaftar'], 401);
        else{
            $passwordTemp = Driver::where('email',$loginData['email'])->first();
            $password = $passwordTemp->password;

            if(!Hash::check($loginData['password'], $password))
                return response (['message' => 'Password salah!'], 401);
            else{
                return response ([
                   'message' => 'Authenticated',
                   'data' => $driver
                ]);
            }
        }   
    }

    public function logout(){
        return response (['message' => 'Logout berhasil']);
    }

    public function index(){
        $driver = Driver::all(); // mengambil semua data driver

        if(count($driver) > 0){
            return response([
                'message' => 'Retrieve All Driver Success',
                'data' => $driver
            ], 200);
        } // return data semua driver dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data driver kosong
    }

    public function show($id){
        $driver = Driver::find($id); // mencari data driver berdasarkan id
        $rating = DB::table('transaksi')
                    ->where("id_driver", $id)
                    ->sum("rating_driver");

        $count = transaksi::where("id_driver", $id)->count();

        $rerata = (double) $rating / $count;

        $driver->rerata_rating = $rerata;

        if(!is_null($driver)){
            return response([
                'message' => 'Retrieve Driver Success',
                'data' => $driver
            ], 200);
        } // return data driver yang ditemukan dalam bentuk json

        return response([
            'message' => 'Driver Not Found',
            'data' => null
        ], 404); // return message saat data driver tidak ditemukan
    }

    public function allDriver(){
        $driver = Driver::where("status_dokumen", "Lengkap")->where("status_driver", "Tersedia")->get(); // mengambil semua data driver

        if(count($driver) > 0){
            return response([
                'message' => 'Retrieve All Driver Success',
                'data' => $driver
            ], 200);
        } // return data semua driver dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); // return message data driver kosong
    }

    public function store(Request $request){
        $storeData = $request->all(); // mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_driver' => 'required',
            'alamat_driver' => 'required',
            'tanggal_lahir' => 'required|date_format:y-m-d',
            'jenis_kelamin' => 'required',
            'email' => 'required|email|unique:driver',
            'no_telp' => 'required|regex:/^(08)[0-9]{11}$/',
            'bahasa' => 'required',
            'pas_foto' => 'nullable|file',
            'sim' => 'nullable|file',
            'surat_bebas_napza' => 'nullable|file',
            'surat_kesehatan_jiwa' => 'nullable|file',
            'surat_kesehatan_jasmani' => 'nullable|file',
            'skck' => 'nullable|file',
            'tarif_driver' => 'required|numeric',
            'status_driver' => 'required',
            'status_dokumen' => 'required',
        ]); // membuat rule validasi input

        if($validate->fails())
            return response(['message' => $validate->errors()], 400); // return error invalid input

        $file_name = time().'.'.$request->pas_foto->extension();
        $request->pas_foto->move(public_path('pas_foto'),$file_name);
        $path = $file_name;
        $storeData['pas_foto'] = $path;

        $file_name = time().'.'.$request->sim->extension();
        $request->sim->move(public_path('foto_sim'),$file_name);
        $path = $file_name;
        $storeData['sim'] = $path;

        $file_name1 = time().'.'.$request->surat_bebas_napza->extension();
        $request->surat_bebas_napza->move(public_path('surat_bebas_napza'),$file_name1);
        $path1 = $file_name1;
        $storeData['surat_bebas_napza'] = $path1;

        $file_name2 = time().'.'.$request->surat_kesehatan_jiwa->extension();
        $request->surat_kesehatan_jiwa->move(public_path('surat_kesehatan_jiwa'),$file_name2);
        $path2 = $file_name2;
        $storeData['surat_kesehatan_jiwa'] = $path2;

        $file_name3 = time().'.'.$request->surat_kesehatan_jasmani->extension();
        $request->surat_kesehatan_jasmani->move(public_path('surat_kesehatan_jasmani'),$file_name3);
        $path3 = $file_name3;
        $storeData['surat_kesehatan_jasmani'] = $path3;

        $file_name4 = time().'.'.$request->skck->extension();
        $request->skck->move(public_path('skck'),$file_name4);
        $path4 = $file_name4;
        $storeData['skck'] = $path4;

        $year = Carbon::now()->format('y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');

        $format = 'DRV-'.$day.$month.$year;
        $storeData['format_id_driver'] = $format;

        $driver = Driver::create($storeData);
        $driver->tampil_id_driver = str_pad($driver->id_driver,3,'0',STR_PAD_LEFT);
        $driver ->format_id_driver = $format.$driver->tampil_id_driver;

        $year = Carbon::createFromFormat('y-m-d', $driver->tanggal_lahir)->format('y');
        $month = Carbon::createFromFormat('y-m-d', $driver->tanggal_lahir)->format('m');
        $day = Carbon::createFromFormat('y-m-d', $driver->tanggal_lahir)->format('d');

        $driver->password = bcrypt($year.$month.$day);
        $driver->save();

        return response([
            'message' => 'Add Driver Success',
            'data' => $driver
        ], 200); // return data driver baru dalam bentuk json
    }

    public function destroy($id){
        $driver = Driver::find($id); // mencari data driver berdasarkan id

        if(is_null($driver)){
            return response([
                'message' => 'Driver Not Found',
                'data' => null
            ], 404); // return message saat data driver tidak ditemukan
        }

        if(File::exists(public_path('pas_foto/'.$driver->pas_foto)) && public_path('foto_sim/'.$driver->sim) 
        && public_path('surat_bebas_napza/'.$driver->surat_bebas_napza) && public_path('surat_kesehatan_jiwa/'.$driver->surat_kesehatan_jiwa) 
        && public_path('surat_kesehatan_jasmani/'.$driver->surat_kesehatan_jasmani) && public_path('skck/'.$driver->skck)) {
            File::delete(public_path('pas_foto/'.$driver->pas_foto));
            File::delete(public_path('foto_sim/'.$driver->sim));
            File::delete(public_path('surat_bebas_napza/'.$driver->surat_bebas_napza));
            File::delete(public_path('surat_kesehatan_jiwa/'.$driver->surat_kesehatan_jiwa));
            File::delete(public_path('surat_kesehatan_jasmani/'.$driver->surat_kesehatan_jasmani));
            File::delete(public_path('skck/'.$driver->skck));
            
            if($driver->delete()){
                return response([
                    'message' => 'Delete Driver Success',
                    'data' => $driver
                ], 200);
            } // return message saat berhasil menghapus data driver
    
            return response([
                'message' => 'Delete Driver Failed',
                'data' => null,
            ], 400); // return message saat gagal menghapus data driver
        }
        else{
            return response([
                'message' => 'Delete Berkas Driver Failed',
            ], 400);
        }
        
    }

    public function update(Request $request, $id){
        $driver = Driver::find($id);
        if (is_null($driver)){
            return response([
                'message' => 'Driver Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'nama_driver' => 'required',
            'alamat_driver' => 'required',
            'tanggal_lahir' => 'required|date_format:y-m-d',
            'jenis_kelamin' => 'required',
            'email' => ['required', "email", Rule::unique('driver')->ignore($driver)],
            'no_telp' => 'required|regex:/^(08)[0-9]{11}$/',
            'bahasa' => 'required',
            'pas_foto' => 'nullable|file',
            'sim' => 'nullable|file',
            'surat_bebas_napza' => 'nullable|file',
            'surat_kesehatan_jiwa' => 'nullable|file',
            'surat_kesehatan_jasmani' => 'nullable|file',
            'skck' => 'nullable|file',
            'tarif_driver' => 'required|numeric',
            'status_driver' => 'required',
            'status_dokumen' => 'required',
            'password' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);
        
        $pas_foto = $driver->pas_foto;
        $sim = $driver->sim;
        $surat_bebas_napza = $driver->surat_bebas_napza;
        $surat_kesehatan_jiwa = $driver->surat_kesehatan_jiwa;
        $surat_kesehatan_jasmani = $driver->surat_kesehatan_jasmani;
        $skck = $driver->skck;

        if(File::exists(public_path('pas_foto/'.$pas_foto)) && public_path('foto_sim/'.$sim) 
        && public_path('surat_bebas_napza/'.$surat_bebas_napza) && public_path('surat_kesehatan_jiwa/'.$surat_kesehatan_jiwa) 
        && public_path('surat_kesehatan_jasmani/'.$surat_kesehatan_jasmani) && public_path('skck/'.$skck)) {
            File::delete(public_path('pas_foto/'.$pas_foto));
            File::delete(public_path('foto_sim/'.$sim));
            File::delete(public_path('surat_bebas_napza/'.$surat_bebas_napza));
            File::delete(public_path('surat_kesehatan_jiwa/'.$surat_kesehatan_jiwa));
            File::delete(public_path('surat_kesehatan_jasmani/'.$surat_kesehatan_jasmani));
            File::delete(public_path('skck/'.$skck));
        }
        else{
            return response([
                'message' => 'Update File Driver Failed',
                'data' => null,
            ], 400); // return message saat gagal update data driver
        }    

        $file_name = time().'.'.$request->pas_foto->extension();
        $request->pas_foto->move(public_path('pas_foto'),$file_name);
        $path = $file_name;
        $updateData['pas_foto'] = $path;

        $file_name = time().'.'.$request->sim->extension();
        $request->sim->move(public_path('foto_sim'),$file_name);
        $path = $file_name;
        $updateData['sim'] = $path;

        $file_name1 = time().'.'.$request->surat_bebas_napza->extension();
        $request->surat_bebas_napza->move(public_path('surat_bebas_napza'),$file_name1);
        $path1 = $file_name1;
        $updateData['surat_bebas_napza'] = $path1;

        $file_name2 = time().'.'.$request->surat_kesehatan_jiwa->extension();
        $request->surat_kesehatan_jiwa->move(public_path('surat_kesehatan_jiwa'),$file_name2);
        $path2 = $file_name2;
        $updateData['surat_kesehatan_jiwa'] = $path2;

        $file_name3 = time().'.'.$request->surat_kesehatan_jasmani->extension();
        $request->surat_kesehatan_jasmani->move(public_path('surat_kesehatan_jasmani'),$file_name3);
        $path3 = $file_name3;
        $updateData['surat_kesehatan_jasmani'] = $path3;

        $file_name4 = time().'.'.$request->skck->extension();
        $request->skck->move(public_path('skck'),$file_name4);
        $path4 = $file_name4;
        $updateData['skck'] = $path4;
        
        $driver->nama_driver = $updateData['nama_driver'];
        $driver->alamat_driver = $updateData['alamat_driver'];
        $driver->tanggal_lahir = $updateData['tanggal_lahir'];
        $driver->jenis_kelamin = $updateData['jenis_kelamin'];
        $driver->email = $updateData['email'];
        $driver->no_telp = $updateData['no_telp'];
        $driver->bahasa = $updateData['bahasa'];
        $driver->pas_foto = $updateData['pas_foto'];
        $driver->sim = $updateData['sim'];
        $driver->surat_bebas_napza = $updateData['surat_bebas_napza'];
        $driver->surat_kesehatan_jiwa = $updateData['surat_kesehatan_jiwa'];
        $driver->surat_kesehatan_jasmani = $updateData['surat_kesehatan_jasmani'];
        $driver->skck = $updateData['skck'];
        $driver->tarif_driver = $updateData['tarif_driver'];
        $driver->status_driver = $updateData['status_driver'];
        $driver->status_dokumen = $updateData['status_dokumen'];
        $driver->password = bcrypt($updateData['password']);
        $driver->tampil_id_driver = str_pad($driver->id_driver,3,'0',STR_PAD_LEFT);

        if($driver->save()) {
            return response([
                'message' => 'Update Driver Success',
                'data' => $driver
            ], 200);
        }

        return response([
            'message' => 'Update Driver Failed',
            'data' => null,
        ], 400);
    }

    public function statusDriver($id){
        $driver = Driver::find($id);
        if($driver->status_driver == "Tersedia") {
            $driver->status_driver = "Tidak Tersedia";
        } 
        else if($driver->status_driver == "Tidak Tersedia") {
            $driver->status_driver = "Tersedia";
        }
        if($driver->save()) {
            return response([
                'message' => 'Update Status Driver Success'
            ], 200);
        }

        return response([
            'message' => 'Update Status Driver Failed'
        ], 400);
    }

    public function editDriverMobile(Request $request, $id) {
        $driver = Driver::find($id);
        if(is_null($driver)){
            return response([
                'message' => 'Driver Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'alamat_driver' => 'required',
            'email' => ['required', "email", Rule::unique('driver')->ignore($driver)],
            'no_telp' => 'required|regex:/^(08)[0-9]{11}$/',
            'bahasa' => 'required',
            'password' => 'nullable'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $driver->alamat_driver = $updateData['alamat_driver'];
        $driver->email = $updateData['email'];
        $driver->no_telp = $updateData['no_telp'];
        $driver->bahasa = $updateData['bahasa'];
        if($updateData['password'] != null) 
            $driver->password = bcrypt($updateData['password']);

        if ($driver->save()) {
            return response([
                'message' => 'Update Driver Success',
                'data' => $driver
        ], 200);
        } // return data driver baru dalam bentuk json

        return response([
            'message' => 'Update Driver Failed',
            'data' => null,
        ], 400);
    }
}
