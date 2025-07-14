<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SparepartController extends Controller
{
    public function index()
    {
        $spareparts = Sparepart::all();

        return response()->json([
            'message' => 'Data sparepart berhasil diambil',
            'status_code' => 200,
            'data' => $spareparts,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            // Validasi input
            $validated = $request->validate([
                'nama_sparepart' => 'required|string|unique:spareparts,nama_sparepart|max:255',
                'harga_satuan' => 'required|numeric|min:0',
            ]);

            $sparepart = Sparepart::create([
                'nama_sparepart' => $validated['nama_sparepart'],
                'harga_satuan' => $validated['harga_satuan'],
            ]);

            return response()->json([
                'message' => 'Sparepart berhasil disimpan',
                'status_code' => 201,
                'data' => $sparepart,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan sparepart: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    // Ambil data satu sparepart
    public function show($id)
    {
        $sparepart = Sparepart::find($id);

        if (!$sparepart) {
            return response()->json([
                'message' => 'Sparepart tidak ditemukan',
                'status_code' => 404,
            ], 404);
        }

        return response()->json([
            'message' => 'Sparepart ditemukan',
            'status_code' => 200,
            'data' => $sparepart,
        ]);
    }

    // Update sparepart
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::guard('api')->user();

            $sparepart = Sparepart::find($id);
            if (!$sparepart) {
                return response()->json([
                    'message' => 'Sparepart tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            $validated = $request->validate([
                'nama_sparepart' => 'sometimes|required|string|max:255|unique:spareparts,nama_sparepart,' . $id,
                'harga_satuan' => 'sometimes|required|numeric|min:0',
            ]);

            $sparepart->update($validated);

            return response()->json([
                'message' => 'Sparepart berhasil diperbarui',
                'status_code' => 200,
                'data' => $sparepart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui sparepart: ' . $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

    // Hapus sparepart
    public function destroy($id)
    {
        try {
            $user = Auth::guard('api')->user();

            if ($user->role !== 'admin') {
                return response()->json([
                    'message' => 'Akses ditolak: hanya admin yang dapat menghapus sparepart',
                    'status_code' => 403,
                ], 403);
            }

            $sparepart = Sparepart::find($id);
            if (!$sparepart) {
                return response()->json([
                    'message' => 'Sparepart tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            $sparepart->delete();

            return response()->json([
                'message' => 'Sparepart berhasil dihapus',
                'status_code' => 200,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus sparepart: ' . $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }
}
