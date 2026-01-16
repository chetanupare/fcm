<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;
use App\Jobs\SendNotificationJob;

class NotificationService
{
    public function notify(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // Dispatch async job for email/push notifications
        SendNotificationJob::dispatch($notification);

        return $notification;
    }

    public function notifyAdmin(string $title, string $message, array $data = []): void
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $this->notify($admin->id, 'admin_alert', $title, $message, $data);
        }
    }

    public function notifyTechnician(int $userId, string $title, string $message, array $data = []): Notification
    {
        return $this->notify($userId, 'job_offer', $title, $message, $data);
    }

    public function notifyCustomer(int $userId, string $title, string $message, array $data = []): Notification
    {
        return $this->notify($userId, 'status_update', $title, $message, $data);
    }
}
