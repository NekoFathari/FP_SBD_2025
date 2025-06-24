<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinjamController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        // Validasi data jika diperlukan
        $request->validate([
            'id_buku' => 'required|integer',
            'id_anggota_pinjam' => 'required|integer',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'required|date',
            'status' => 'required|string|in:Pinjam,Kembali',
        ]);
        // Kita cek 1 user hanya bisa meminjam max 3 buku
        $pinjamCount = DB::table('riwayat')
            ->where('id_anggota_pinjam', $data['id_anggota_pinjam'])
            ->where('status', 'Pinjam')
            ->count();
        if ($pinjamCount >= 3) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Anda sudah meminjam maksimal 3 buku',
            ], 400);
        }


        // Insert data ke MySQL menggunakan query builder
        DB::table('riwayat')->insert([
            'id_buku' => $data['id_buku'],
            'id_anggota_pinjam' => $data['id_anggota_pinjam'],
            'tanggal_pinjam' => $data['tanggal_pinjam'],
            'tanggal_kembali' => $data['tanggal_kembali'],
            'status' => $data['status'], // enum: Pinjam, Kembali
        ]);

        // kurangi stok buku
        DB::table('buku')->where('id_buku', $data['id_buku'])->decrement('stok');

        //return response
        return response()->json([
            'result' => 'OK',
            'message' => 'Pinjaman berhasil disimpan',
            'response' => [
                'id_buku' => $data['id_buku'],
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali' => $data['tanggal_kembali'],
                'status' => $data['status'],
            ],
            'stok_buku' => DB::table('buku')->where('id_buku', $data['id_buku'])->value('stok'),
        ])->setStatusCode(200);
    }

    public function show($id)
    {
        // Batasi hanya 2 buku yang bisa dipinjam
        $pinjam = DB::table('riwayat')
            ->where('id_anggota_pinjam', $id)
            ->where('status', 'Pinjam')
            ->get();

        return response()->json([
            'result' => 'OK',
            'pinjam' => $pinjam,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->all();

        // Validasi data jika diperlukan
        $request->validate([
            'id_riwayat' => 'required|integer',
            'id_buku' => 'required|integer',
            'status' => 'required|string|in:Pinjam,Kembali',
        ]);  

        // Update data ke MySQL menggunakan query builder
        DB::table('riwayat')
            ->where('id_riwayat', $data['id_riwayat'])
            ->update([
                'status' => $data['status'],
            ]);

        // Jika statusnya Kembali, tambahkan stok buku
        if ($data['status'] === 'Kembali') {
            DB::table('buku')
                ->where('id_buku', $data['id_buku'])
                ->increment('stok');
        }

        return response()->json([
            'result' => 'OK',
            'message' => 'Pinjaman berhasil diperbarui',
        ]);
    }

}


