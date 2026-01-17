<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    protected bool $customerEnabled;
    protected bool $technicianEnabled;

    protected string $fcmServerKey;

    public function __construct()
    {
        $this->customerEnabled = Setting::get('customer_push_enabled', true);
        $this->technicianEnabled = Setting::get('technician_push_enabled', true);
        $this->fcmServerKey = config('services.fcm.server_key') ?: Setting::get('fcm_server_key', '');
    }

    /**
     * Send notification to customer using Laravel Notifications and FCM
     */
    public function sendToCustomer(User $user, $notification): bool
    {
        if (!$this->customerEnabled || $user->role !== 'customer') {
            return false;
        }

        try {
            // Send via Laravel notifications (database)
            $user->notify($notification);

            // Send via FCM if configured
            if ($this->fcmServerKey && $user->fcm_token) {
                $this->sendViaFCM($user->fcm_token, $notification->title ?? '', $notification->message ?? '', $notification->data ?? []);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification to customer', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Send notification to technician using Laravel Notifications and FCM
     */
    public function sendToTechnician(User $user, $notification): bool
    {
        if (!$this->technicianEnabled || $user->role !== 'technician') {
            return false;
        }

        try {
            // Send via Laravel notifications (database)
            $user->notify($notification);

            // Send via FCM if configured
            if ($this->fcmServerKey && $user->fcm_token) {
                $this->sendViaFCM($user->fcm_token, $notification->title ?? '', $notification->message ?? '', $notification->data ?? []);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification to technician', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers($users, $notification): int
    {
        $sent = 0;
        foreach ($users as $user) {
            if ($user->role === 'customer' && $this->customerEnabled) {
                try {
                    $user->notify($notification);
                    $sent++;
                } catch (\Exception $e) {
                    Log::error('Failed to send notification', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                    ]);
                }
            } elseif ($user->role === 'technician' && $this->technicianEnabled) {
                try {
                    $user->notify($notification);
                    $sent++;
                } catch (\Exception $e) {
                    Log::error('Failed to send notification', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                    ]);
                }
            }
        }
        return $sent;
    }

    /**
     * Send notification via Firebase Cloud Messaging
     */
    protected function sendViaFCM(string $token, string $title, string $message, array $data = []): bool
    {
        if (!$this->fcmServerKey) {
            return false;
        }

        try {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                    'sound' => 'default',
                ],
                'data' => $data,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', ['token' => substr($token, 0, 10) . '...']);
                return true;
            }

            Log::error('FCM notification failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 10) . '...',
            ]);
            return false;
        }
    }
}
