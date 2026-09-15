<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', User::class);

        $users = User::query()
            ->when($request->input('search'), fn ($q, $s) => $q->where(fn ($q2) => $q2->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")))
            ->latest()
            ->paginate(15);

        return response()->json($users);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage', User::class);

        $data = $request->validate(['status' => ['required', 'in:active,suspended']]);

        $user->forceFill(['status' => $data['status']])->save();

        return response()->json(['user' => $user]);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage', User::class);

        $data = $request->validate(['role' => ['required', 'in:user,admin']]);

        $user->forceFill(['role' => $data['role']])->save();

        return response()->json(['user' => $user]);
    }
}