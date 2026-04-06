namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handlePaymongo(Request $request)
    {
        $payload = $request->all();
        $type = $payload['data']['attributes']['type']; // e.g., checkout_session.payment.paid

        Log::info('PayMongo Webhook Received: ' . $type);

        if ($type === 'checkout_session.payment.paid') {
            $attributes = $payload['data']['attributes']['data']['attributes'];
            $planId = $attributes['metadata']['plan_id'] ?? null;
            $userId = $attributes['metadata']['user_id'] ?? null;

            if ($planId && $userId) {
                $this->activateSubscription($userId, $planId);
            }
        }

        // Always return a 200 response to tell PayMongo you received it
        return response()->json(['status' => 'success'], 200);
    }

    protected function activateSubscription($userId, $planId)
    {
        $plan = DB::table('subscription_plans')->where('id', $planId)->first();
        
        // Logic to stack/add the subscription as we did in previous steps
        $latestExpiry = DB::table('user_subscriptions')
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->max('expires_at');

        $startDate = $latestExpiry ? \Carbon\Carbon::parse($latestExpiry) : now();
        
        DB::table('user_subscriptions')->updateOrInsert(
            ['user_id' => $userId, 'plan_id' => $planId, 'status' => 'active'],
            [
                'starts_at' => $startDate,
                'expires_at' => $startDate->copy()->addDays($plan->duration_days),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}