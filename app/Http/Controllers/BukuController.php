<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    public function save(Request $request)
    {
        $data = $request->all();

        // Insert data ke MySQL menggunakan query builder
        $id = DB::table('buku')->insertGetId($data);

        // Ambil data yang baru saja dibuat
        $buku = DB::table('buku')->where('id_buku', $id)->first();

        return response()->json([
            'result' => 'OK',
            'message' => 'Buku berhasil disimpan',
            'buku' => $buku,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // Validasi data jika diperlukan
        $request->validate([
            'judul' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'penulis' => 'required|string|max:100',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|integer',
            'stok' => 'required|integer|min:0',
        ]);

        // Insert data ke MySQL menggunakan query builder
        $id = DB::table('buku')->insertGetId($data);

        // Ambil data yang baru saja dibuat
        $buku = DB::table('buku')->where('id_buku', $id)->first();

        return response()->json([
            'result' => 'OK',
            'message' => 'Buku berhasil disimpan',
            'buku' => $buku,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        DB::table('buku')->where('id_buku', $id)->update($data);

        $buku = DB::table('buku')->where('id_buku', $id)->first();

        return response()->json([
            'result' => 'OK',
            'message' => 'Buku berhasil diperbarui',
            'buku' => $buku,
        ]);
    }

    public function destroy($id)
    {
        DB::table('buku')->where('id_buku', $id)->delete();

        return response()->json([
            'result' => 'OK',
            'message' => 'Buku berhasil dihapus',
        ]);
    }

    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $buku = DB::table('buku')->paginate(9, ['*'], 'page', $page);

        return response()->json([
            'result' => 'OK',
            'buku' => $buku,
        ]);
    }

    public function show($id)
    {
        $buku = DB::table('buku')->where('id_buku', $id)->first();

        return response()->json([
            'result' => 'OK',
            'buku' => $buku,
        ]);
    }
}