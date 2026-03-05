<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Services\PushNotificationService;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
    public function store(Request $request, PushNotificationService $service): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'p256dh' => ['required', 'string'],
            'auth' => ['required', 'string'],
        ]);

        $subscription = $service->subscribe(
            $request->user(),
            $request->endpoint,
            $request->p256dh,
            $request->auth,
        );

        return response()->json($subscription, 201);
    }

    public function destroy(Request $request, PushNotificationService $service): Response
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $service->unsubscribe($request->user(), $request->endpoint);

        return response()->noContent();
    }

    public function vapidPublicKey(): JsonResponse
    {
        return response()->json([
            'public_key' => config('services.vapid.public_key'),
        ]);
    }
}
