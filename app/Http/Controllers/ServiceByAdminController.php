<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceByAdminRequest;
use App\Models\ServiceByAdmin;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceByAdminController extends Controller
{
    public function index()
    {
        $services = ServiceByAdmin::with('serviceRequest')->get();

        return response()->json([
            'message' => 'Data layanan berhasil diambil',
            'status_code' => 200,
            'data' => $services,
        ]);
    }

    public function store(StoreServiceByAdminRequest $request)
    {
        try {

            $validated = $request->validate([
                'service_id' => 'required|exists:service_requests,id',
                'nama_servis' => 'required|string|max:255',
                'biaya_servis' => 'required|numeric|min:0',
                'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $binaryProof = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $binaryProof = file_get_contents($request->file('bukti_pembayaran')->getRealPath());
            }

            // Simpan data ServiceByAdmin
            $service = ServiceByAdmin::create([
                'service_id' => $validated['service_id'],
                'nama_servis' => $validated['nama_servis'],
                'biaya_servis' => $validated['biaya_servis'],
                'bukti_pembayaran' => $binaryProof,
            ]);

            // ✅ Update status di tabel service_requests
            $serviceRequest = \App\Models\ServiceRequest::find($validated['service_id']);
            if ($serviceRequest) {
                $serviceRequest->update(['status' => 'sedang diperbaiki']);
            }

            // Hitung total bayar
            $service->hitungTotalBayar();

            return response()->json([
                'message' => 'Layanan berhasil ditambahkan dan status service diperbarui.',
                'status_code' => 201,
                'data' => $service,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan layanan: ' . $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

    public function show($id)
    {
        $service = ServiceByAdmin::with([
            'serviceRequest',
            'serviceSpareparts.sparepart'
        ])->find($id);

        if (!$service) {
            return response()->json([
                'message' => 'Layanan tidak ditemukan',
                'status_code' => 404,
            ], 404);
        }

        $user = Auth::guard('api')->user();
        $isAdmin = $user->role_id === 1;
        $isOwner = $service->serviceRequest->user_id === $user->id;

        if (!($isAdmin || $isOwner)) {
            return response()->json([
                'message' => 'Tidak diizinkan melihat layanan ini',
                'status_code' => 403,
            ], 403);
        }

        $data = $service->toArray();
        $data['bukti_pembayaran'] = $service->bukti_pembayaran
            ? base64_encode($service->bukti_pembayaran)
            : null;

        // ✅ Encode photo dari relasi service_request jika berupa binary
        if (isset($data['service_request']['photo']) && !empty($data['service_request']['photo'])) {
            $data['service_request']['photo'] = base64_encode($service->serviceRequest->photo);
        }


        return response()->json([
            'message' => 'Detail layanan ditemukan',
            'status_code' => 200,
            'data' => $data,
        ]);
    }



    public function update(Request $request, $serviceId)
    {
        try {
            Log::info("Update dipanggil untuk serviceId: $serviceId");

            $service = ServiceByAdmin::with('serviceRequest')
                ->where('service_id', $serviceId)
                ->firstOrFail();

            Log::info("Service ditemukan: ID " . $service->id);

            $updated = false;

            if ($request->has('total_bayar')) {
                $service->total_bayar = $request->input('total_bayar');
                Log::info("Update total_bayar: " . $service->total_bayar);
                $updated = true;
            }

            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                Log::info("File bukti_pembayaran diterima: " . $file->getClientOriginalName());

                $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                    return response()->json([
                        'message' => 'Format bukti pembayaran tidak didukung.',
                        'status_code' => 422,
                    ], 422);
                }

                $service->bukti_pembayaran = file_get_contents($file->getRealPath());
                Log::info("Bukti pembayaran disimpan");
                $updated = true;

                $serviceRequest = ServiceRequest::find($service->service_id);
                if ($serviceRequest) {
                    $serviceRequest->status = 'berhasil';
                    $serviceRequest->save();
                    Log::info("Status serviceRequest diupdate ke 'berhasil'");
                }
            }

            if ($updated) {
                $saveResult = $service->save();

                if (!$saveResult || !$service->wasChanged()) {
                    return response()->json([
                        'message' => 'Gagal memperbarui data. Tidak ada perubahan yang tersimpan.',
                        'status_code' => 500,
                    ], 500);
                }

                Log::info("Data service berhasil disimpan");
            } else {
                return response()->json([
                    'message' => 'Tidak ada data yang dikirim untuk diperbarui.',
                    'status_code' => 400,
                ], 400);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'status_code' => 200,
                'data' => [
                    'id' => $service->id,
                    'service_id' => $service->service_id,
                    'nama_servis' => $service->nama_servis,
                    'biaya_servis' => $service->biaya_servis,
                    'total_bayar' => $service->total_bayar,
                    'created_at' => $service->created_at,
                    'bukti_pembayaran' => $service->bukti_pembayaran
                        ? base64_encode($service->bukti_pembayaran)
                        : null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error update: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal update: ' . $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }



    public function destroy($id)
    {
        try {
            $user = Auth::guard('api')->user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'message' => 'Akses ditolak: hanya admin yang dapat menghapus layanan',
                    'status_code' => 403,
                ], 403);
            }

            $service = ServiceByAdmin::find($id);
            if (!$service) {
                return response()->json([
                    'message' => 'Layanan tidak ditemukan',
                    'status_code' => 404,
                ], 404);
            }

            $service->delete();

            return response()->json([
                'message' => 'Layanan berhasil dihapus',
                'status_code' => 200,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus layanan: ' . $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

    public function showByServiceId($serviceId)
    {
        $service = ServiceByAdmin::with(['serviceRequest', 'serviceSpareparts.sparepart'])
            ->where('service_id', $serviceId)
            ->first();

        if (!$service) {
            return response()->json([
                'message' => 'Layanan berdasarkan service_id tidak ditemukan',
                'status_code' => 404,
            ], 404);
        }

        $user = Auth::guard('api')->user();
        $isAdmin = $user->role_id === 1;
        $isOwner = $service->serviceRequest->user_id === $user->id;

        if (!($isAdmin || $isOwner)) {
            return response()->json([
                'message' => 'Tidak diizinkan melihat layanan ini',
                'status_code' => 403,
            ], 403);
        }

        // Konversi ke array agar bisa modifikasi isinya
        $data = $service->toArray();

        // Encode bukti_pembayaran jika bentuknya binary
        $data['bukti_pembayaran'] = $service->bukti_pembayaran
            ? base64_encode($service->bukti_pembayaran)
            : null;

        // Encode photo di relasi serviceRequest jika binary
        if (
            isset($service->serviceRequest->photo) &&
            !empty($service->serviceRequest->photo)
        ) {
            $data['service_request']['photo'] = base64_encode($service->serviceRequest->photo);
        }

        return response()->json([
            'message' => 'Detail layanan berdasarkan service_id ditemukan',
            'status_code' => 200,
            'data' => $data,
        ]);
    }
}
