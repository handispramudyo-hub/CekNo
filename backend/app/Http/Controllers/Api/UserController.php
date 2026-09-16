<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        $items = $request->user()
            ->searchHistories()
            ->with('phoneNumber')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json(['history' => $items]);
    }

    public function reports(Request $request): JsonResponse
    {
        $items = $request->user()
            ->reports()
            ->with('phoneNumber')
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function reviews(Request $request): JsonResponse
    {
        $items = $request->user()
            ->reviews()
            ->with('phoneNumber')
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function tags(Request $request): JsonResponse
    {
        $items = $request->user()
            ->phoneTags()
            ->with(['tag', 'phoneNumber'])
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->update($data);

        return response()->json(['user' => $request->user()]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Kata sandi saat ini tidak cocok.',
                'errors' => ['current_password' => ['Kata sandi saat ini tidak cocok.']],
            ], 422);
        }

        $user->update(['password' => $data['password']]);

        return response()->json(['message' => 'Kata sandi berhasil diperbarui.']);
    }
}