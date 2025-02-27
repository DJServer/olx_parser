<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessOlxIdJob;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate(['url' => 'required|url']);

        $user = Auth::user();

        $announcement = Announcement::firstOrCreate(
            ['url' => $request->url],
            ['title' => 'Pending...']
        );

        Subscription::firstOrCreate([
            'user_id' => $user->id,
            'announcement_id' => $announcement->id,
        ]);

        ProcessOlxIdJob::dispatch($announcement)->onQueue('olx');

        return response()->json(['message' => 'URL добавлен, OLX ID будет найден в фоне.']);
    }
}
