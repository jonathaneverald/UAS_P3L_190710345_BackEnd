<!DOCTYPE html>
<html>
<head>
	<title>Nota Transaksi</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
	<style type="text/css">
		table tr td,
		table tr th{
			font-size: 9pt;
            padding-left: 5px; 
		}
        table, tr{
            width: 100%;
            border: 0.5px solid black;
            border-collapse: collapse;
        }
	</style>
	<center>
		<h5>Nota Transaksi <br>Atma Jogja Rental</h4>
        <hr style="border: 1px solid black">
	</center>
 
	<table>
		<tbody>
            <tr style="border-bottom: none !important;">
				<th colspan="6" class="text-center">Atma Rental</th>
			</tr>
            <tr style="border-top: none !important;">
                <td colspan="2">{{ $transaksi[0]->format_id_transaksi }}</td>
                <td></td>
                <td colspan="2"></td>
                <td>{{ $transaksi[0]->tanggal_transaksi_sewa_mobil}}</td>
            </tr>
            <tr>
                <td colspan="2">Cust 
                    <br>CS
                    <br>DRV
                </td>
                <td>{{ $transaksi[0]->customer->nama_customer }} 
                    <br>{{ $transaksi[0]->pegawai->nama_pegawai }}
                    @if ($transaksi[0]->id_driver != null)
                        <br>{{ $transaksi[0]->driver->nama_driver }}
                    @else
                        <br>-
                    @endif 
                </td>
                <td valign="top" colspan="2">PRO:</td>
                @if ($transaksi[0]->id_promo != null)
                    <td valign="top">{{ $transaksi[0]->promo->kode_promo }}</td>
                @else
                    <td valign="top">-</td>
                @endif
            </tr>
            <tr></tr>
            <tr>
                <td colspan="2"></td>
                <th colspan="2" class="text-center">Nota Transaksi</th>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">Tgl Mulai 
                </td>
                <td>{{ $transaksi[0]->tanggal_mulai_sewa_mobil }} 
                </td>
                <td colspan="2"></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2">Tgl Selesai
                </td>
                <td>{{ $transaksi[0]->tanggal_selesai_sewa_mobil }}
                </td>
                <td colspan="2"></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2">Tgl Pengembalian
                </td>
                <td>{{ $transaksi[0]->tanggal_pengembalian_mobil }}
                </td>
                <td colspan="2"></td>
                <td></td>
            </tr>
            <tr>
                <th colspan="2">Item</th>
                <th>Satuan</th>
                <th colspan="2">Durasi</th>
                <th>Sub Total</th>
            </tr>
            <tr>
                <td colspan="2">{{ $transaksi[0]->mobil->nama_mobil_sewa }}</td>
                <td>{{ $transaksi[0]->mobil->harga_sewa_mobil }}</td>
                <td colspan="2">{{ $selisih = Carbon\Carbon::createFromFormat('y-m-d H:i', $transaksi[0]->tanggal_mulai_sewa_mobil)->diffInDays(Carbon\Carbon::createFromFormat('y-m-d H:i', $transaksi[0]->tanggal_selesai_sewa_mobil)) }} hari</td>
                <td> {{ $subTotal1 = $selisih *  $transaksi[0]->mobil->harga_sewa_mobil }}</td>
            </tr>
            <tr>
                @if ($transaksi[0]->id_driver != null)
                    <td colspan="2">Driver {{ $transaksi[0]->driver->nama_driver }}</td>
                    <td>{{ $transaksi[0]->driver->tarif_driver }}</td>
                    <td colspan="2">{{ $selisih }} hari</td>
                    <td> {{ $subTotal2 = $selisih *  $transaksi[0]->driver->tarif_driver }}</td>
                @else
                    <td colspan="2">Driver -</td>
                    <td>0</td>
                    <td colspan="2">0 hari</td>
                    <td> {{ $subTotal2 = 0 }}</td>
                @endif
            </tr>
            <tr>
                <td colspan="2"></td>
                <td></td>
                <td colspan="2"></td>
                <th> {{ $total = $subTotal1  +  $subTotal2 }}</th>
            </tr>
            <tr></tr>
            <tr>
                <td valign="top" colspan="2" rowspan="3">Cust</td>
                <td valign="top" rowspan="3">CS</td>
                <td colspan="2">Disc</td>
                @if ($transaksi[0]->id_promo != null)
                    <td> {{ $diskon = $total * $transaksi[0]->promo->potongan_promo / 100.0 }}</td>
                @else
                    <td> {{ $diskon = 0 }}</td>
                @endif
            </tr>
            <tr>
                <td colspan="2">Denda</td>
                @if ($transaksi[0]->denda_penyewaan != null)
                    <td> {{ $denda_penyewaan = $transaksi[0]->denda_penyewaan }}</td>
                @else
                    <td> {{ $denda_penyewaan = 0 }}</td>
                @endif
            </tr>
            <tr style="border-bottom: none !important;">
                <th colspan="2">Total</th>
                <th> {{ $total-$diskon+$denda_penyewaan }}</th>
            </tr>
            <tr style="border-top: none !important;">
                <td colspan="2">{{ $transaksi[0]->customer->nama_customer }}</td>
                <td>{{ $transaksi[0]->pegawai->nama_pegawai }}</td>
                <td colspan="2"></td>
                <td></td>
            </tr>
		</tbody>
	</table>
</body>
</html>