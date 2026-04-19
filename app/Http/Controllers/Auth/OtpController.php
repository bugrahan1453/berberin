<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        User::updateOrCreate(
            ['phone' => $request->phone],
            [
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(5),
            ]
        );

        // SMS entegrasyonu hazır olana kadar OTP response'da dönüyor
        return response()->json([
            'success' => true,
            'data' => ['otp' => $otp],
            'message' => 'OTP kodu gönderildi.',
        ]);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || $user->otp_code !== $request->otp) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Geçersiz OTP kodu.',
            ], 422);
        }

        if ($user->otp_expires_at < now()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'OTP kodunun süresi dolmuş.',
            ], 422);
        }

        // OTP'yi temizle
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Doğrulama başarılı.',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Çıkış yapıldı.',
        ]);
    }
}
