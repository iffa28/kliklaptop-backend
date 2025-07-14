<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function store(StoreProfileRequest $request)
    {
        $user = Auth::user();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile_photos', 'public');
        }

        $profile = Profile::create([
            'user_id' => $user->id,
            'phone' => $request->phone,
            'photo' => $photoPath,
        ]);

        return response()->json([
            'message' => 'Profil berhasil dibuat',
            'status_code' => 201,
            'data' => $profile,
        ], 201);
    }

    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profil belum tersedia',
                'status_code' => 404,
            ], 404);
        }

        return response()->json([
            'message' => 'Profil ditemukan',
            'status_code' => 200,
            'data' => $profile,
        ]);
    }

    public function update(StoreProfileRequest $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'message' => 'Profil tidak ditemukan',
                'status_code' => 404,
            ], 404);
        }

        if ($request->hasFile('photo')) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }

            $photoPath = $request->file('photo')->store('profile_photos', 'public');
            $profile->photo = $photoPath;
        }

        $profile->phone = $request->phone ?? $profile->phone;
        $profile->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'status_code' => 200,
            'data' => $profile,
        ]);
    }
}
