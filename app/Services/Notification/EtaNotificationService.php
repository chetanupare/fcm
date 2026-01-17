<?php

namespace App\Services\Notification;

use App\Models\Job;
use App\Models\User;
use App\Services\Notification\MultiChannelNotificationService;
use Illuminate\Support\Facades\Log;

class EtaNotificationService
{
    protected MultiChannelNotificationService $notificationService;

    public function __construct(MultiChannelNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send ETA update notification to customer
     *
     * @param Job $job
     * @param string|null $customMessage
     */
    public function sendEtaUpdate(Job $job, ?string $customMessage = null): void
    {
        if (!$job->customer) {
            return;
        }

        $data = $this->prepareEtaData($job);

        if ($customMessage) {
            $data['custom_sms_message'] = $customMessage;
            $data['custom_push_message'] = $customMessage;
        }

        $this->notificationService->send(
            $job->customer,
            'technician_en_route',
            'ETA Update',
            $customMessage ?: 'Technician ETA has been updated',
            $data
        );

        Log::info('ETA update notification sent', [
            'job_id' => $job->id,
            'customer_id' => $job->customer->id,
        ]);
    }

    /**
     * Send notification when technician arrives
     *
     * @param Job $job
     */
    public function sendTechnicianArrived(Job $job): void
    {
        if (!$job->customer) {
            return;
        }

        $data = $this->prepareArrivalData($job);

        $this->notificationService->send(
            $job->customer,
            'technician_arrived',
            'Technician Arrived',
            'Your technician has arrived at the location',
            $data
        );

        Log::info('Technician arrived notification sent', [
            'job_id' => $job->id,
            'customer_id' => $job->customer->id,
        ]);
    }

    /**
     * Send notification when technician is approaching (within 10 minutes)
     *
     * @param Job $job
     */
    public function sendTechnicianApproaching(Job $job): void
    {
        if (!$job->customer) {
            return;
        }

        $data = $this->prepareEtaData($job);

        $this->notificationService->send(
            $job->customer,
            'technician_en_route',
            'Technician Approaching',
            'Your technician is approaching and will arrive shortly',
            array_merge($data, [
                'custom_sms_message' => 'Hi! Your technician is just 10 minutes away. Please be ready at the location.',
                'custom_push_title' => 'Technician Approaching',
                'custom_push_message' => 'Your technician will arrive in about 10 minutes. Please be ready!'
            ])
        );

        Log::info('Technician approaching notification sent', [
            'job_id' => $job->id,
            'customer_id' => $job->customer->id,
        ]);
    }

    /**
     * Send delay notification
     *
     * @param Job $job
     * @param string $reason
     */
    public function sendDelayNotification(Job $job, string $reason): void
    {
        if (!$job->customer) {
            return;
        }

        $data = $this->prepareEtaData($job);
        $data['delay_reason'] = $reason;

        $this->notificationService->send(
            $job->customer,
            'technician_en_route',
            'Service Delay',
            "There's a slight delay: {$reason}",
            array_merge($data, [
                'custom_sms_message' => "Hi! There's a slight delay with your service: {$reason}. We'll update you with new ETA soon.",
                'custom_push_title' => 'Service Delay',
                'custom_push_message' => "There's a slight delay: {$reason}. We'll update you with new ETA soon."
            ])
        );

        Log::info('Delay notification sent', [
            'job_id' => $job->id,
            'customer_id' => $job->customer->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Send proactive ETA reminder (when ETA is available)
     *
     * @param Job $job
     */
    public function sendEtaReminder(Job $job): void
    {
        if (!$job->customer) {
            return;
        }

        $data = $this->prepareEtaData($job);

        $this->notificationService->send(
            $job->customer,
            'technician_en_route',
            'Service Reminder',
            'Your technician is on the way',
            array_merge($data, [
                'custom_sms_message' => 'Hi! Just a reminder - your technician is on the way. Track real-time: your-app-link',
                'custom_push_title' => 'Service Reminder',
                'custom_push_message' => 'Your technician is on the way. Tap to track in real-time.'
            ])
        );

        Log::info('ETA reminder notification sent', [
            'job_id' => $job->id,
            'customer_id' => $job->customer->id,
        ]);
    }

    /**
     * Prepare ETA data for notifications
     *
     * @param Job $job
     * @return array
     */
    protected function prepareEtaData(Job $job): array
    {
        $data = [
            'ticket_id' => $job->ticket_id,
            'job_id' => $job->id,
        ];

        if ($job->technician) {
            $data['technician_name'] = $job->technician->user->name;
            $data['technician_phone'] = $job->technician->user->phone;
        }

        if ($job->estimated_duration_minutes && $job->distance_km) {
            $etaMinutes = $job->estimated_duration_minutes + ($job->distance_km * 2); // Rough estimate
            $data['eta'] = ceil($etaMinutes / 15) * 15 . ' mins'; // Round to nearest 15 mins
            $data['eta_minutes'] = $etaMinutes;
        }

        if ($job->distance_km) {
            $data['distance_km'] = round($job->distance_km, 1);
        }

        return $data;
    }

    /**
     * Prepare arrival data for notifications
     *
     * @param Job $job
     * @return array
     */
    protected function prepareArrivalData(Job $job): array
    {
        $data = [
            'ticket_id' => $job->ticket_id,
            'job_id' => $job->id,
        ];

        if ($job->technician) {
            $data['technician_name'] = $job->technician->user->name;
            $data['technician_phone'] = $job->technician->user->phone;
        }

        return $data;
    }
}