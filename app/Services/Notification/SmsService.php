<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class SmsService
{
    protected ?string $provider;
    protected ?string $apiKey;
    protected ?string $apiSecret;
    protected ?string $fromNumber;

    public function __construct()
    {
        $this->provider = Setting::get('sms_provider', 'twilio'); // twilio, messagebird, etc.
        $this->apiKey = config('services.twilio.sid') ?: Setting::get('sms_api_key');
        $this->apiSecret = config('services.twilio.token') ?: Setting::get('sms_api_secret');
        $this->fromNumber = config('services.twilio.from') ?: Setting::get('sms_from_number');
    }

    /**
     * Send SMS message
     * 
     * @param string $to Phone number (E.164 format)
     * @param string $message Message content
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        if (!$this->apiKey || !$this->apiSecret) {
            Log::warning('SMS service not configured');
            return false;
        }

        try {
            return match($this->provider) {
                'twilio' => $this->sendViaTwilio($to, $message),
                'messagebird' => $this->sendViaMessageBird($to, $message),
                default => $this->sendViaTwilio($to, $message),
            };
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'error' => $e->getMessage(),
                'to' => $to,
                'provider' => $this->provider,
            ]);
            return false;
        }
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $to, string $message): bool
    {
        $accountSid = $this->apiKey;
        $authToken = $this->apiSecret;

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $this->fromNumber,
                'To' => $to,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            Log::info('SMS sent via Twilio', ['to' => $to]);
            return true;
        }

        Log::error('Twilio SMS failed', [
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return false;
    }

    /**
     * Send via MessageBird
     */
    protected function sendViaMessageBird(string $to, string $message): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "AccessKey {$this->apiKey}",
        ])->post('https://rest.messagebird.com/messages', [
            'originator' => $this->fromNumber,
            'recipients' => [$to],
            'body' => $message,
        ]);

        if ($response->successful()) {
            Log::info('SMS sent via MessageBird', ['to' => $to]);
            return true;
        }

        Log::error('MessageBird SMS failed', [
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return false;
    }
}
