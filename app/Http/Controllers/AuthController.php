<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
     public function register(RegisterRequest $request)
    {

        try {
            // Buat user baru
            $user = new User();
            $user->name     = $request->username;
            $user->email    = $request->email;
            $user->password = Hash::make($request->password);
            $user->role_id  = 2;
            $user->save();

            // Return response sukses
            return response()->json([
                'status_code' => 201,
                'message' => 'User created successfully',
                'data'    => $user,

            ], 201);
        } catch (Exception $e) {
            // Return response gagal
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::guard('api')->user();

            $formatedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ];

            return response()->json([
                'message' => 'User ditemukan',
                'status_code' => 200,
                'data' => $formatedUser
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah',
                'status_code' => 401,
                'data' => null
            ], 401);
        }

        try {
            $user = Auth::guard('api')->user();

            $formatedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'token' => $token
            ];

            return response()->json([
                'message' => 'Login berhasil',
                'status_code' => 200,
                'data' => $formatedUser
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_code' => 500,
                'data' => null
            ], 500);
        }
    }
}
