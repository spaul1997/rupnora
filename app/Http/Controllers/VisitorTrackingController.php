<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorTrackingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page_path' => ['required', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:150'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        VisitorLog::create([
            ...$data,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 2000, ''),
            'visited_at' => now(),
        ]);

        return response()->json(['tracked' => true], 201);
    }
}
