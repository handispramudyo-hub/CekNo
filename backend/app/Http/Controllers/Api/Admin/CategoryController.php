<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('manage', Category::class);

        return response()->json(['categories' => Category::orderBy('risk_weight', 'desc')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage', Category::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'risk_weight' => ['required', 'integer', 'between:0,100'],
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'description' => $data['description'] ?? null,
            'risk_weight' => $data['risk_weight'],
        ]);

        return response()->json(['category' => $category], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $this->authorize('manage', Category::class);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'risk_weight' => ['sometimes', 'integer', 'between:0,100'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $category->update($data);

        return response()->json(['category' => $category]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('manage', Category::class);

        $category->delete();

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}