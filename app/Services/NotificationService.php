<?php

namespace App\Services;

use App\Models\LineRequest;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send SMS notification.
     */
    public function sendSms(string $phone, string $message): bool
    {
        // TODO: Integrate with SMS gateway (e.g., Twilio, Africa's Talking)
        // For now, just log and save to database
        SystemNotification::create([
            'type' => 'sms',
            'channel' => 'sms',
            'recipient' => $phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        Log::info('SMS notification queued', ['phone' => $phone]);

        return true;
    }

    /**
     * Send USSD notification.
     */
    public function sendUssd(string $phone, string $ussdCode): bool
    {
        SystemNotification::create([
            'type' => 'ussd',
            'channel' => 'ussd',
            'recipient' => $phone,
            'message' => $ussdCode,
            'status' => 'pending',
        ]);

        Log::info('USSD notification queued', ['phone' => $phone, 'code' => $ussdCode]);

        return true;
    }

    /**
     * Notify customer about request status.
     */
    public function notifyCustomer(LineRequest $request, string $message): void
    {
        $customer = $request->customer;

        SystemNotification::create([
            'user_id' => $customer->user_id,
            'line_request_id' => $request->id,
            'type' => 'sms',
            'channel' => 'sms',
            'recipient' => $customer->phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        $this->sendSms($customer->phone, $message);
    }

    /**
     * Notify agent about new request.
     */
    public function notifyAgent(User $agentUser, LineRequest $request): void
    {
        $message = "New line request #{$request->request_number} available. Line type: {$request->line_type->value}";

        SystemNotification::create([
            'user_id' => $agentUser->id,
            'line_request_id' => $request->id,
            'type' => 'push',
            'channel' => 'push',
            'recipient' => $agentUser->phone ?? $agentUser->email,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    /**
     * Send confirmation code to customer.
     */
    public function sendConfirmationCode(LineRequest $request, string $code): void
    {
        $message = "Agent {$request->agent->user->name} anakusajili. Confirmation code: {$code}";

        $this->notifyCustomer($request, $message);
    }
}
