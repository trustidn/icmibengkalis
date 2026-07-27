<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Menerima unggahan gambar dari rich editor (isi artikel & halaman statis).
 * Diizinkan bagi siapa pun yang boleh menulis konten: editor/admin, atau
 * anggota yang boleh membuat opini (lihat PostPolicy::create).
 */
class EditorUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user->can('create', Post::class) || $user->can('pages.manage'),
            403
        );

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        $path = $validated['image']->store('editor/'.now()->format('Y/m'), 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }
}
