<?php

namespace App\Services\Notification;

class PushTemplateService
{
    /**
     * Get push notification template for notification type
     *
     * @param string $type
     * @param array $data
     * @return array
     */
    public function getTemplate(string $type, array $data = []): array
    {
        return match($type) {
            'booking_confirmation' => $this->bookingConfirmation($data),
            'technician_assigned' => $this->technicianAssigned($data),
            'technician_en_route' => $this->technicianEnRoute($data),
            'technician_arrived' => $this->technicianArrived($data),
            'quote_ready' => $this->quoteReady($data),
            'service_complete' => $this->serviceComplete($data),
            'payment_reminder' => $this->paymentReminder($data),
            default => $this->defaultTemplate($data),
        };
    }

    /**
     * Booking confirmation push notification
     */
    protected function bookingConfirmation(array $data): array
    {
        $ticketId = $data['ticket_id'] ?? '';
        $device = $data['device'] ?? '';

        return [
            'title' => 'Service Request Confirmed',
            'message' => "Your request #{$ticketId} for {$device} has been confirmed. We'll assign a technician soon.",
            'data' => [
                'type' => 'booking_confirmation',
                'ticket_id' => $ticketId,
                'action' => 'view_ticket'
            ]
        ];
    }

    /**
     * Technician assigned push notification
     */
    protected function technicianAssigned(array $data): array
    {
        $technicianName = $data['technician_name'] ?? '';
        $eta = $data['eta'] ?? '';

        return [
            'title' => 'Technician Assigned',
            'message' => "{$technicianName} has been assigned to your service. " . ($eta ? "ETA: {$eta}" : ""),
            'data' => [
                'type' => 'technician_assigned',
                'technician_name' => $technicianName,
                'eta' => $eta,
                'action' => 'view_tracking'
            ]
        ];
    }

    /**
     * Technician en route push notification
     */
    protected function technicianEnRoute(array $data): array
    {
        $technicianName = $data['technician_name'] ?? '';
        $eta = $data['eta'] ?? '';

        return [
            'title' => 'Technician On The Way',
            'message' => "{$technicianName} is heading to your location. " . ($eta ? "Expected arrival: {$eta}" : ""),
            'data' => [
                'type' => 'technician_en_route',
                'technician_name' => $technicianName,
                'eta' => $eta,
                'action' => 'view_tracking'
            ]
        ];
    }

    /**
     * Technician arrived push notification
     */
    protected function technicianArrived(array $data): array
    {
        $technicianName = $data['technician_name'] ?? '';

        return [
            'title' => 'Technician Arrived',
            'message' => "{$technicianName} has arrived and will begin service shortly.",
            'data' => [
                'type' => 'technician_arrived',
                'technician_name' => $technicianName,
                'action' => 'view_status'
            ]
        ];
    }

    /**
     * Quote ready push notification
     */
    protected function quoteReady(array $data): array
    {
        $amount = $data['quote_amount'] ?? '';
        $currency = $data['currency'] ?? '₹';

        return [
            'title' => 'Quote Ready',
            'message' => "Your service quote is ready" . ($amount ? " for {$currency}{$amount}" : "") . ". Please review and approve.",
            'data' => [
                'type' => 'quote_ready',
                'quote_amount' => $amount,
                'action' => 'view_quote'
            ]
        ];
    }

    /**
     * Service complete push notification
     */
    protected function serviceComplete(array $data): array
    {
        $amount = $data['total_amount'] ?? '';
        $currency = $data['currency'] ?? '₹';

        return [
            'title' => 'Service Completed',
            'message' => "Your device repair is complete" . ($amount ? ". Total: {$currency}{$amount}" : "") . ". Please complete payment.",
            'data' => [
                'type' => 'service_complete',
                'total_amount' => $amount,
                'action' => 'view_payment'
            ]
        ];
    }

    /**
     * Payment reminder push notification
     */
    protected function paymentReminder(array $data): array
    {
        $amount = $data['amount_due'] ?? '';
        $currency = $data['currency'] ?? '₹';

        return [
            'title' => 'Payment Reminder',
            'message' => "Payment of {$currency}{$amount} is pending. Please complete payment to finalize service.",
            'data' => [
                'type' => 'payment_reminder',
                'amount_due' => $amount,
                'action' => 'view_payment'
            ]
        ];
    }

    /**
     * Default push notification template
     */
    protected function defaultTemplate(array $data): array
    {
        $title = $data['title'] ?? 'Notification';
        $message = $data['message'] ?? '';

        return [
            'title' => $title,
            'message' => $message,
            'data' => [
                'type' => 'general',
                'action' => 'view_app'
            ]
        ];
    }
}