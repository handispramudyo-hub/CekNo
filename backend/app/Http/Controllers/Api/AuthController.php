<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Kredensial tidak cocok.'], 401);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'Akun Anda dinonaktifkan.'], 403);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function forgot(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $status = Password::sendResetLink(['email' => $request->email]);

        return response()->json([
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Jika email terdaftar, tautan reset kata sandi telah dikirim.'
                : 'Jika email terdaftar, tautan reset kata sandi telah dikirim.',
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Tautan reset tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json([
            'message' => 'Kata sandi berhasil direset. Silakan masuk dengan kata sandi baru.',
        ]);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'hash' => ['required', 'string'],
        ]);

        $user = User::find($data['id']);

        if (! $user || ! hash_equals(sha1($user->email), $data['hash'])) {
            return response()->json(['message' => 'Tautan verifikasi tidak valid.'], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi.']);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email berhasil diverifikasi.']);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah diverifikasi.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Tautan verifikasi email telah dikirim.',
        ]);
    }
}
