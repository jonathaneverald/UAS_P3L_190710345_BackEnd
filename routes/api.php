<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});




Route::post('logincustomer', 'Api\CustomerController@login');
Route::get('logoutcustomer', 'Api\CustomerController@logout');
Route::get('customer', 'Api\CustomerController@index');
Route::get('customer/{id}', 'Api\CustomerController@show');
Route::post('customer', 'Api\CustomerController@store');
Route::post('customer/{id}', 'Api\CustomerController@update');
Route::delete('customer/{id}', 'Api\CustomerController@destroy');
Route::post('customer/mobile/{id}','Api\customerController@editCustomerMobile');

Route::get('role', 'Api\RoleController@index');
Route::get('role/{id}', 'Api\RoleController@show');
Route::post('role', 'Api\RoleController@store');
Route::put('role/{id}', 'Api\RoleController@update');
Route::delete('role/{id}', 'Api\RoleController@destroy');

Route::get('jadwal_pegawai', 'Api\Jadwal_PegawaiController@index');
Route::get('jadwal_pegawai/{id}', 'Api\Jadwal_PegawaiController@show');
Route::post('jadwal_pegawai', 'Api\Jadwal_PegawaiController@store');
Route::put('jadwal_pegawai/{id}', 'Api\Jadwal_PegawaiController@update');
Route::delete('jadwal_pegawai/{id}', 'Api\Jadwal_PegawaiController@destroy');

Route::get('promo', 'Api\PromoController@index');
Route::get('promo/{id}', 'Api\PromoController@show');
Route::post('promo', 'Api\PromoController@store');
Route::put('promo/{id}', 'Api\PromoController@update');
Route::delete('promo/{id}', 'Api\PromoController@destroy');
Route::get('promoAll','Api\promoController@all');

Route::get('pemilik_mobil', 'Api\Pemilik_MobilController@index');
Route::get('pemilik_mobil/{id}', 'Api\Pemilik_MobilController@show');
Route::post('pemilik_mobil', 'Api\Pemilik_MobilController@store');
Route::put('pemilik_mobil/{id}', 'Api\Pemilik_MobilController@update');
Route::delete('pemilik_mobil/{id}', 'Api\Pemilik_MobilController@destroy');

Route::post('logindriver', 'Api\DriverController@login');
Route::get('logoutdriver', 'Api\DriverController@logout');
Route::get('driver', 'Api\DriverController@index');
Route::get('driver/{id}', 'Api\DriverController@show');
Route::get('driverAll','Api\driverController@allDriver');
Route::post('driver', 'Api\DriverController@store');
Route::post('driver/{id}', 'Api\DriverController@update');
Route::get('driver/status/{id}','Api\driverController@statusDriver');
Route::delete('driver/{id}', 'Api\DriverController@destroy');
Route::post('driver/mobile/{id}','Api\driverController@editDriverMobile');

Route::post('loginpegawai', 'Api\PegawaiController@login');
Route::get('logoutpegawai', 'Api\PegawaiController@logout');
Route::get('pegawai', 'Api\PegawaiController@index');
Route::get('pegawai/{id}', 'Api\PegawaiController@show');
Route::post('pegawai', 'Api\PegawaiController@store');
Route::post('pegawai/{id}', 'Api\PegawaiController@update');
Route::delete('pegawai/{id}', 'Api\PegawaiController@destroy');

Route::get('detail_jadwal_pegawai', 'Api\Detail_Jadwal_PegawaiController@index');
Route::get('detail_jadwal_pegawai/{id}', 'Api\Detail_Jadwal_PegawaiController@show');
Route::post('detail_jadwal_pegawai/{id}', 'Api\Detail_Jadwal_PegawaiController@store');
Route::put('detail_jadwal_pegawai/{idPegawai}', 'Api\Detail_Jadwal_PegawaiController@update');
Route::delete('detail_jadwal_pegawai/{idPegawai}/{idJadwal}', 'Api\Detail_Jadwal_PegawaiController@destroy');

Route::get('mobil', 'Api\MobilController@index');
Route::get('mobil_kontrak', 'Api\MobilController@kontrakMobil');
Route::get('mobil/{id}', 'Api\MobilController@show');
Route::post('mobil', 'Api\MobilController@store');
Route::post('mobil/{id}', 'Api\MobilController@update');
Route::delete('mobil/{id}', 'Api\MobilController@destroy');
Route::get('mobilAll','Api\mobilController@all');

Route::get('transaksi', 'Api\TransaksiController@index');
Route::get('transaksi/{id}', 'Api\TransaksiController@show');
Route::get('transaksi/driver/{id}','Api\TransaksiController@showDriver');
Route::post('transaksi', 'Api\TransaksiController@store');
Route::post('transaksi/pembayaran/{id}','Api\TransaksiController@pembayaran');
Route::get('transaksi/cetak_nota/{id}','Api\TransaksiController@cetak_nota');
Route::post('transaksi/{id}', 'Api\TransaksiController@update');
Route::post('transaksiUpdate/{id}', 'Api\TransaksiController@updateTransaksiCustomer');
Route::delete('transaksi/{id}', 'Api\TransaksiController@destroy');
Route::post('transaksi/mobile/laporan_top_driver','Api\transaksiController@laporanTopDriver');
Route::post('transaksi/mobile/laporan_performa_driver','Api\transaksiController@laporanPerformaDriver');
Route::post('transaksi/mobile/laporan_top_customer','Api\transaksiController@laporanTopCustomer');
Route::post('transaksi/mobile/laporan_detail_pendapatan','Api\transaksiController@laporanDetailPendapatan');
Route::post('transaksi/mobile/laporan_sewa_mobil','Api\transaksiController@laporanSewaMobil');