<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceSparepartRequest;
use App\Models\ServiceSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServiceSparepartController extends Controller
{
    // public function store(StoreServiceSparepartRequest $request)
    // {
    //     try {
    //         $data = $request->validated();

    //         // Cek apakah kombinasi service_by_admin_id dan sparepart_id sudah ada
    //         $exists = ServiceSparepart::where('service_by_admin_id', $data['service_by_admin_id'])
    //             ->where('sparepart_id', $data['sparepart_id'])
    //             ->exists();

    //         if ($exists) {
    //             return response()->json([
    //                 'message' => 'Sparepart ini sudah ditambahkan pada service tersebut.',
    //                 'status_code' => 409,
    //             ], 409);
    //         }

    //         $relasi = ServiceSparepart::create($data);

    //         return response()->json([
    //             'message' => 'Sparepart berhasil ditambahkan ke service.',
    //             'status_code' => 201,
    //             'data' => $relasi,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
    //             'status_code' => 500,
    //         ], 500);
    //     }
    // }

    public function getByServiceId($id)
    {
        try {
            $spareparts = ServiceSparepart::with('sparepart')
                ->where('service_by_admin_id', $id)
                ->get();

            if ($spareparts->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada sparepart terkait service ini.',
                    'status_code' => 404,
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Data sparepart untuk service ditemukan.',
                'status_code' => 200,
                'data' => $spareparts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }


    public function bulkStore(Request $request)
    {
        $items = $request->all();

        // Log awal untuk memastikan data dikirim
        Log::info('📥 Request bulk sparepart:', ['payload' => $items]);

        // Cek apakah payload valid dan tidak kosong
        if (!is_array($items) || empty($items)) {
            return response()->json([
                'message' => 'Data tidak boleh kosong.',
                'status_code' => 422,
            ], 422);
        }

        $success = 0;
        $failed = [];

        foreach ($items as $index => $item) {
            // Log masing-masing item
            Log::info("🔍 Validating item $index", $item);

            $validator = Validator::make($item, [
                'service_by_admin_id' => 'required|integer|exists:service_by_admin,id',
                'sparepart_id' => 'required|integer|exists:spareparts,id',
            ]);

            if ($validator->fails()) {
                $failed[] = [
                    'index' => $index,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // Cek jika sudah ada
            $exists = ServiceSparepart::where('service_by_admin_id', $item['service_by_admin_id'])
                ->where('sparepart_id', $item['sparepart_id'])
                ->exists();

            if ($exists) {
                $failed[] = [
                    'index' => $index,
                    'errors' => ['Sparepart sudah ditambahkan ke service.'],
                ];
                continue;
            }

            // Simpan data
            ServiceSparepart::create([
                'service_by_admin_id' => $item['service_by_admin_id'],
                'sparepart_id' => $item['sparepart_id'],
            ]);

            $success++;
        }

        return response()->json([
            'message' => 'Proses selesai.',
            'status_code' => 200,
            'success_count' => $success,
            'failed_items' => $failed,
        ], 200);
    }
}
