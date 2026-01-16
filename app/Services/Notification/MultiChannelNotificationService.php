<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\Notification;
use App\Services\Notification\SmsService;
use App\Services\Notification\PushNotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class MultiChannelNotificationService
{
    protected SmsService $smsService;
    protected PushNotificationService $pushService;
    protected NotificationService $notificationService;

    public function __construct(
        SmsService $smsService,
        PushNotificationService $pushService,
        NotificationService $notificationService
    ) {
        $this->smsService = $smsService;
        $this->pushService = $pushService;
        $this->notificationService = $notificationService;
    }

    /**
     * Send notification via multiple channels based on user preferences
     * 
     * @param User $user
     * @param string $type Notification type (booking_confirmation, technician_assigned, etc.)
     * @param string $title
     * @param string $message
     * @param array $data
     * @param array $channels Override user preferences (sms, email, push)
     * @return Notification
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?array $channels = null
    ): Notification {
        // Get user preferences or use defaults
        $preferences = $user->notification_preferences ?? [
            'sms' => true,
            'email' => true,
            'push' => true,
        ];

        // Override with provided channels if specified
        if ($channels !== null) {
            $preferences = array_merge($preferences, $channels);
        }

        // Respect quiet hours
        if ($this->isQuietHours($user)) {
            // Only send critical notifications during quiet hours
            if (!in_array($type, ['critical', 'emergency'])) {
                $preferences['sms'] = false;
                $preferences['push'] = false;
            }
        }

        // Create notification record
        $notification = $this->notificationService->notify(
            $user->id,
            $type,
            $title,
            $message,
            $data
        );

        // Send via SMS
        if ($preferences['sms'] ?? true) {
            $this->sendSms($user, $message, $data);
        }

        // Send via Email
        if ($preferences['email'] ?? true) {
            $this->sendEmail($user, $type, $title, $message, $data);
        }

        // Send via Push
        if ($preferences['push'] ?? true) {
            $this->sendPush($user, $type, $title, $message, $data);
        }

        return $notification;
    }

    /**
     * Send SMS notification
     */
    protected function sendSms(User $user, string $message, array $data): void
    {
        if (!$user->phone) {
            return;
        }

        try {
            $this->smsService->send($user->phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(User $user, string $type, string $title, string $message, array $data): void
    {
        if (!$user->email) {
            return;
        }

        try {
            $template = $this->getEmailTemplate($type);
            
            Mail::send($template, [
                'user' => $user,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ], function ($mail) use ($user, $title) {
                $mail->to($user->email)
                    ->subject($title);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send push notification
     */
    protected function sendPush(User $user, string $type, string $title, string $message, array $data): void
    {
        try {
            $notification = new \App\Notifications\GenericNotification($title, $message, $data);
            $this->pushService->sendToCustomer($user, $notification);
        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get email template name for notification type
     */
    protected function getEmailTemplate(string $type): string
    {
        return match($type) {
            'booking_confirmation' => 'emails.booking-confirmation',
            'technician_assigned' => 'emails.technician-assigned',
            'technician_en_route' => 'emails.technician-en-route',
            'technician_arrived' => 'emails.technician-arrived',
            'quote_ready' => 'emails.quote-ready',
            'service_complete' => 'emails.service-complete',
            'payment_reminder' => 'emails.payment-reminder',
            default => 'emails.notification',
        };
    }

    /**
     * Check if current time is within user's quiet hours
     */
    protected function isQuietHours(User $user): bool
    {
        $quietHours = $user->quiet_hours ?? ['start' => '22:00', 'end' => '08:00'];
        
        if (!isset($quietHours['start']) || !isset($quietHours['end'])) {
            return false;
        }

        $now = now();
        $start = \Carbon\Carbon::parse($quietHours['start']);
        $end = \Carbon\Carbon::parse($quietHours['end']);

        // Handle quiet hours that span midnight
        if ($start->greaterThan($end)) {
            return $now->greaterThanOrEqualTo($start) || $now->lessThanOrEqualTo($end);
        }

        return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
    }
}
