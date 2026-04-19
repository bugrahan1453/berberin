<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show()
    {
        return response()->json([
            'success' => true,
            'data' => auth()->user(),
            'message' => '',
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'unique:users,email,' . auth()->id()],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:500'],
            'lat' => ['sometimes', 'nullable', 'numeric'],
            'lng' => ['sometimes', 'nullable', 'numeric'],
            'fcm_token' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = auth()->user();
        $user->update($request->only(['name', 'email', 'avatar', 'lat', 'lng', 'fcm_token']));

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'Profil güncellendi.',
        ]);
    }
}
