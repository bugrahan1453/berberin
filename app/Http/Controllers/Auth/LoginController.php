<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Telefon numarası veya şifre hatalı.',
            ], 401);
        }

        if ($user->status === 'banned') {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Hesabınız kalıcı olarak engellenmiştir.',
            ], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Hesabınız geçici olarak askıya alınmıştır.',
            ], 403);
        }

        // Eski tokenları temizle (tek oturum politikası)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Giriş başarılı.',
        ]);
    }
}
