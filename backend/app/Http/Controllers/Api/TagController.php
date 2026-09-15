<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhoneNumber;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(PhoneNumber $number): JsonResponse
    {
        return response()->json(['tags' => $number->tags]);
    }

    public function store(Request $request, PhoneNumber $number): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'category_slug' => ['nullable', 'string', 'max:40'],
        ]);

        $tag = Tag::firstOrCreate(
            ['slug' => str()->slug($data['name'])],
            ['name' => $data['name'], 'status' => 'approved'],
        );

        $phoneTag = $number->phoneTags()->firstOrCreate(
            ['tag_id' => $tag->id, 'user_id' => auth()->id()],
            ['status' => 'approved'],
        );

        return response()->json(['tag' => $tag, 'phone_tag' => $phoneTag], 201);
    }
}