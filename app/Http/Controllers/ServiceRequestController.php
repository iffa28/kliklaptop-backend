<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceRequestController extends Controller
{

    public function index()
    {
        try {
            $requests = ServiceRequest::with('user')->get();

            $data = $requests->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user' => $item->user ? [
                        'id' => $item->user->id,
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                    ] : null,
                    'jenis_laptop' => $item->jenis_laptop,
                    'deskripsi_keluhan' => $item->deskripsi_keluhan,
                    'photo' => $item->photo ? base64_encode($item->photo) : null,
                    'status' => $item->status,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'message' => 'Daftar permintaan service berhasil diambil',
                'status_code' => 200,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    /**
     * Simpan data permintaan service.
     */
    public function store(StoreServiceRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();

            // Simpan gambar dalam bentuk binary
            $binaryImage = null;
            if ($request->hasFile('photo')) {
                $binaryImage = file_get_contents($request->file('photo')->getRealPath());
            }

            $serviceRequest = ServiceRequest::create([
                'user_id' => $user->id,
                'jenis_laptop' => $request->jenis_laptop,
                'deskripsi_keluhan' => $request->deskripsi_keluhan,
                'photo' => $binaryImage,
                'status' => 'menunggu konfirmasi',
            ]);

            return response()->json([
                'message' => 'Permintaan service berhasil disimpan',
                'status_code' => 201,
                'data' => [
                    'id' => $serviceRequest->id,
                    'jenis_laptop' => $serviceRequest->jenis_laptop,
                    'deskripsi_keluhan' => $serviceRequest->deskripsi_keluhan,
                    'photo' => $binaryImage ? base64_encode($binaryImage) : null, // jika ingin dikirim ke frontend
                    'status' => $serviceRequest->status,
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
        $serviceRequest = ServiceRequest::with('user')->find($id);

        if (!$serviceRequest) {
            return response()->json([
                'message' => 'Permintaan service tidak ditemukan',
                'status_code' => 404,
                'data' => null,
            ]);
        }

        return response()->json([
            'message' => 'Permintaan service ditemukan',
            'status_code' => 200,
            'data' => [
                'id' => $serviceRequest->id,
                'user_id' => $serviceRequest->user_id,
                'jenis_laptop' => $serviceRequest->jenis_laptop,
                'deskripsi_keluhan' => $serviceRequest->deskripsi_keluhan,
                'status' => $serviceRequest->status,
                'photo' => $serviceRequest->photo ? base64_encode($serviceRequest->photo) : null,
                'created_at' => $serviceRequest->created_at->toDateTimeString(),
            ]
        ]);
    }

    public function history()
    {
        try {
            $user = Auth::guard('api')->user();

            $history = ServiceRequest::with('user')
                ->where('user_id', $user->id)
                ->where('status', 'berhasil')
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $history->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user' => $item->user ? [
                        'id' => $item->user->id,
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                    ] : null,
                    'jenis_laptop' => $item->jenis_laptop,
                    'deskripsi_keluhan' => $item->deskripsi_keluhan,
                    'photo' => $item->photo ? base64_encode($item->photo) : null,
                    'status' => $item->status,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'message' => 'Riwayat service berhasil diambil',
                'status_code' => 200,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'status_code' => 500,
                'data' => null,
            ], 500);
        }
    }

    public function listservice()
    {
        try {
            $user = Auth::guard('api')->user();

            $history = ServiceRequest::with('user')
                ->where('user_id', $user->id) // ← tambahkan ini!
                ->whereIn('status', ['menunggu konfirmasi', 'dikonfirmasi', 'sedang diperbaiki', 'perbaikan selesai'])
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $history->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user' => $item->user ? [
                        'id' => $item->user->id,
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                    ] : null,
                    'jenis_laptop' => $item->jenis_laptop,
                    'deskripsi_keluhan' => $item->deskripsi_keluhan,
                    'photo' => $item->photo ? base64_encode($item->photo) : null,
                    'status' => $item->status,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'message' => 'Data service aktif berhasil diambil',
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
