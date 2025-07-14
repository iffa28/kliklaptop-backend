<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();

            $data = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'nama_produk' => $product->nama_produk,
                    'deskripsi' => $product->deskripsi,
                    'stok' => $product->stok,
                    'harga' => (int) $product->harga,
                    'photo' => $product->foto_produk ? base64_encode($product->foto_produk) : null,
                ];
            });

            return response()->json([
                'message' => 'List produk berhasil diambil',
                'status_code' => 200,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data produk: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ]);
        }
    }


    public function store(StoreProductRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();

            // Simpan gambar dalam bentuk binary
            $binaryImage = null;
            if ($request->hasFile('foto_produk')) {
                $binaryImage = file_get_contents($request->file('foto_produk')->getRealPath());
            }

            $product = Product::create([
                'nama_produk' => $request->nama_produk,
                'deskripsi' => $request->deskripsi,
                'stok' => $request->stok,
                'harga' => $request->harga,
                'foto_produk' => $binaryImage,
            ]);

            return response()->json([
                'message' => 'Produk berhasil disimpan',
                'status_code' => 201,
                'data' => [
                    'id' => $product->id,
                    'nama_produk' => $product->nama_produk,
                    'deskripsi' => $product->deskripsi,
                    'stok' => $product->stok,
                    'harga' => $product->harga,
                    'photo' => $binaryImage ? base64_encode($binaryImage) : null, // jika ingin dikirim ke frontend
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    public function edit($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan',
                'status_code' => 404,
                'data' => null,
            ]);
        }

        return response()->json([
            'message' => 'Produk ditemukan',
            'status_code' => 200,
            'data' => [
                'id' => $product->id,
                'nama_produk' => $product->nama_produk,
                'deskripsi' => $product->deskripsi,
                'stok' => $product->stok,
                'harga' => $product->harga,
                'foto_produk' => $product->foto_produk ? base64_encode($product->foto_produk) : null,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $product->nama_produk = $request->nama_produk ?? $product->nama_produk;
            $product->deskripsi = $request->deskripsi ?? $product->deskripsi;
            $product->stok = $request->stok ?? $product->stok;
            $product->harga = $request->harga ?? $product->harga;

            if ($request->hasFile('foto_produk')) {
                $product->foto_produk = file_get_contents($request->file('foto_produk')->getRealPath());
            }

            $product->save();

            return response()->json([
                'message' => 'Produk berhasil diperbarui',
                'status_code' => 200,
                'data' => [
                    'id' => $product->id,
                    'nama_produk' => $product->nama_produk,
                    'deskripsi' => $product->deskripsi,
                    'stok' => $product->stok,
                    'harga' => $product->harga,
                    'photo' => $product->foto_produk ? base64_encode($product->foto_produk) : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => 'Produk berhasil dihapus',
                'status_code' => 200,
                'data' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus produk: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ]);
        }
    }
}
