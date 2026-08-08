<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueCatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $raw = $request->getContent();
        abort_unless(hash_equals((string) config('utaone.revenuecat_authorization'), (string) $request->header('Authorization', '')), 401, 'Invalid RevenueCat webhook authentication');
        $secret = (string) config('utaone.revenuecat_signing_secret');
        if ($secret !== '') {
            abort_unless(hash_equals(hash_hmac('sha256', $raw, $secret), (string) $request->header('X-RevenueCat-Webhook-Signature', '')), 401, 'Invalid RevenueCat webhook authentication');
        }
        $payload = json_decode($raw, true);
        $event = $payload['event'] ?? null;
        abort_unless(is_array($event) && isset($event['id'],$event['app_user_id']), 422, 'Invalid webhook payload');
        $active = ['INITIAL_PURCHASE', 'RENEWAL', 'UNCANCELLATION', 'PRODUCT_CHANGE', 'SUBSCRIPTION_EXTENDED'];
        $inactive = ['EXPIRATION'];
        $type = (string) ($event['type'] ?? 'UNKNOWN');
        $isActive = in_array($type, $active, true) ? 1 : (in_array($type, $inactive, true) ? 0 : null);
        try {
            DB::transaction(function () use ($event, $type, $raw, $isActive) {
                DB::table('webhook_events')->insert(['id' => (string) $event['id'], 'event_type' => $type, 'payload_json' => $raw, 'received_at' => now()]);
                if ($isActive !== null) {
                    DB::table('subscriptions')->upsert([['app_user_id' => (string) $event['app_user_id'], 'entitlement_id' => config('utaone.revenuecat_entitlement_id'), 'is_active' => $isActive, 'product_id' => $event['product_id'] ?? null, 'expires_at' => $event['expiration_at_ms'] ?? null, 'environment' => $event['environment'] ?? null, 'last_event_id' => (string) $event['id'], 'updated_at' => now()]], ['app_user_id'], ['entitlement_id', 'is_active', 'product_id', 'expires_at', 'environment', 'last_event_id', 'updated_at']);
                }
            });
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return response()->json(['received' => true, 'duplicate' => true]);
            } throw $e;
        }

        return response()->json(['received' => true]);
    }
}
