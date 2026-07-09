<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class FirebaseTestController extends Controller
{
    public function send(Request $request, FirebaseNotificationService $firebase)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['nullable', 'string', 'max:100'],
            'body' => ['nullable', 'string', 'max:255'],
            'app_type' => ['nullable', 'in:driver,company,store,customer'],
        ]);

        $result = $firebase->sendToUser(
            userId: $data['user_id'] ?? $request->user()->id,
            title: $data['title'] ?? 'Test Notification',
            body: $data['body'] ?? 'Firebase notification is working.',
            data: [
                'type' => 'test',
            ],
            appType: $data['app_type'] ?? null,
        );

        return response()->json($result);
    }
}
