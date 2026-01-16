<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected bool $customerEnabled;
    protected bool $technicianEnabled;

    public function __construct()
    {
        $this->customerEnabled = Setting::get('customer_push_enabled', true);
        $this->technicianEnabled = Setting::get('technician_push_enabled', true);
    }

    /**
     * Send notification to customer using Laravel Notifications
     */
    public function sendToCustomer(User $user, $notification): bool
    {
        if (!$this->customerEnabled || $user->role !== 'customer') {
            return false;
        }

        try {
            $user->notify($notification);
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
     * Send notification to technician using Laravel Notifications
     */
    public function sendToTechnician(User $user, $notification): bool
    {
        if (!$this->technicianEnabled || $user->role !== 'technician') {
            return false;
        }

        try {
            $user->notify($notification);
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
}
