<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiPembelianRequest;
use App\Http\Requests\UpdateTransaksiPembelianRequest;
use App\Models\TransaksiPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransaksiPembelianController extends Controller
{
    public function index()
    {
        $data = TransaksiPembelian::with('product', 'user')->get();

        $result = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'user_id' => $item->user_id,
                'nama_user' => $item->user->name ?? 'Pengguna tidak ditemukan',
                'nama_produk' => $item->product->nama_produk ?? 'Produk tidak ditemukan',
                'metode_pembayaran' => $item->metode_pembayaran,
                'status' => $item->status,
                'bukti_pembayaran' => $item->bukti_pembayaran
                    ? base64_encode($item->bukti_pembayaran)
                    : null,
                'created_at' => $item->created_at ? $item->created_at->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'message' => 'Daftar transaksi berhasil diambil',
            'status_code' => 200,
            'data' => $result,
        ]);
    }


    public function store(StoreTransaksiPembelianRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $binaryProof = null;

            if ($request->hasFile('bukti_pembayaran')) {
                $binaryProof = file_get_contents($request->file('bukti_pembayaran')->getRealPath());
            }

            $transaksi = TransaksiPembelian::create([
                'product_id' => $request->product_id,
                'user_id' => $user->id,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => 'menunggu penjemputan',
                'bukti_pembayaran' => $binaryProof,
            ]);

            return response()->json([
                'message' => 'Transaksi berhasil ditambahkan',
                'status_code' => 201,
                'data' => [
                    'id' => $transaksi->id,
                    'product_id' => $transaksi->product_id,
                    'user_id' => $transaksi->user_id,
                    'metode_pembayaran' => $transaksi->metode_pembayaran,
                    'status' => $transaksi->status,
                    'bukti_pembayaran' => $binaryProof ? base64_encode($binaryProof) : null,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan transaksi: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    public function show($id)
    {
        $transaksi = TransaksiPembelian::with('product', 'user')->find($id);

        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan',
                'status_code' => 404,
                'data' => null,
            ]);
        }

        return response()->json([
            'message' => 'Detail transaksi ditemukan',
            'status_code' => 200,
            'data' => [
                'id' => $transaksi->id,
                'product_id' => $transaksi->product_id,
                'user_id' => $transaksi->user_id,
                'product' => [
                    'id' => $transaksi->product->id,
                    'nama_produk' => $transaksi->product->nama_produk,
                ],
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'status' => $transaksi->status,
                'bukti_pembayaran' => $transaksi->bukti_pembayaran
                    ? base64_encode($transaksi->bukti_pembayaran)
                    : null,
                'created_at' => $transaksi->created_at ? $transaksi->created_at->toDateTimeString() : null,
            ]
        ]);
    }

    public function update(UpdateTransaksiPembelianRequest $request, $id)
    {
        $transaksi = TransaksiPembelian::find($id);
        if (! $transaksi) {
            return response()->json([
                'message'     => 'Transaksi tidak ditemukan',
                'status_code' => 404,
                'data'        => null,
            ], 404);
        }

        // Perbaharui hanya yang di‐fill
        if ($request->filled('metode_pembayaran')) {
            $transaksi->metode_pembayaran = $request->metode_pembayaran;
        }

        if ($request->filled('status')) {
            $transaksi->status = $request->status;
        }

        if ($request->hasFile('bukti_pembayaran')) {
            $transaksi->bukti_pembayaran = file_get_contents(
                $request->file('bukti_pembayaran')->getRealPath()
            );
        }

        $transaksi->save();

        // Kembalikan response dengan data terbaru
        return response()->json([
            'message'     => 'Transaksi berhasil diperbarui',
            'status_code' => 200,
            'data'        => [
                'id'               => $transaksi->id,
                'product_id'       => $transaksi->product_id,
                'user_id'          => $transaksi->user_id,
                'nama_user'        => $transaksi->user->name,
                'nama_produk'      => $transaksi->product->nama_produk,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'status'           => $transaksi->status,
                'bukti_pembayaran' => $transaksi->bukti_pembayaran
                    ? base64_encode($transaksi->bukti_pembayaran)
                    : null,
            ],
        ], 200);
    }

    public function destroy($id)
    {
        $transaksi = TransaksiPembelian::find($id);
        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan',
                'status_code' => 404,
                'data' => null,
            ]);
        }

        try {
            $transaksi->delete();

            return response()->json([
                'message' => 'Transaksi berhasil dihapus',
                'status_code' => 200,
                'data' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ]);
        }
    }

    public function history()
    {
        $user = Auth::guard('api')->user();

        $data = TransaksiPembelian::with('product', 'user')
            ->where('user_id', $user->id)
            ->where('status', 'transaksi berhasil')
            ->get();

        $result = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'user_id' => $item->user_id,
                'nama_user' => $item->user->name ?? 'Pengguna tidak ditemukan',
                'nama_produk' => $item->product->nama_produk ?? 'Produk tidak ditemukan',
                'metode_pembayaran' => $item->metode_pembayaran,
                'status' => $item->status,
                'bukti_pembayaran' => $item->bukti_pembayaran
                    ? base64_encode($item->bukti_pembayaran)
                    : null,
                'created_at' => $item->created_at ? $item->created_at->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'message' => 'Riwayat transaksi berhasil diambil',
            'status_code' => 200,
            'data' => $result,
        ]);
    }

    public function listpembelian()
    {
        try {
            $user = Auth::guard('api')->user();

            $pembelian = TransaksiPembelian::with(['product', 'user'])
                ->where('user_id', $user->id)
                ->where('status', 'menunggu penjemputan')
                ->orderByDesc('created_at')
                ->get();

            $data = $pembelian->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'user_id' => $item->user_id,
                    'nama_user' => $item->user->name ?? 'Tidak ditemukan',
                    'nama_produk' => $item->product->nama_produk ?? 'Tidak ditemukan',
                    'metode_pembayaran' => $item->metode_pembayaran,
                    'status' => $item->status,
                    'bukti_pembayaran' => $item->bukti_pembayaran ? base64_encode($item->bukti_pembayaran) : null,
                    'created_at' => optional($item->created_at)->toDateTimeString(),
                ];
            });

            return response()->json([
                'message' => 'Data pembelian aktif berhasil diambil',
                'status_code' => 200,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ]);
        }
    }
}
