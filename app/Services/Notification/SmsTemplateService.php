<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\Job;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class SmsTemplateService
{
    /**
     * Get SMS template for notification type
     *
     * @param string $type
     * @param array $data
     * @return string
     */
    public function getTemplate(string $type, array $data = []): string
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
     * Booking confirmation SMS
     */
    protected function bookingConfirmation(array $data): string
    {
        $ticketId = $data['ticket_id'] ?? '';
        $device = $data['device'] ?? '';
        $scheduledDate = isset($data['scheduled_date']) ? date('M j, Y', strtotime($data['scheduled_date'])) : '';

        return "Hi! Your service request #{$ticketId} for {$device} has been confirmed. " .
               ($scheduledDate ? "Scheduled for: {$scheduledDate}. " : "") .
               "We'll assign a technician soon. Track: your-app-link";
    }

    /**
     * Technician assigned SMS
     */
    protected function technicianAssigned(array $data): string
    {
        $technicianName = $data['technician_name'] ?? '';
        $technicianPhone = $data['technician_phone'] ?? '';
        $eta = $data['eta'] ?? '';

        return "Hi! {$technicianName} has been assigned to your service. " .
               ($technicianPhone ? "Call: {$technicianPhone}. " : "") .
               ($eta ? "ETA: {$eta}. " : "") .
               "Track your service at: your-app-link";
    }

    /**
     * Technician en route SMS
     */
    protected function technicianEnRoute(array $data): string
    {
        $technicianName = $data['technician_name'] ?? '';
        $eta = $data['eta'] ?? '';

        return "{$technicianName} is on the way! " .
               ($eta ? "Expected arrival: {$eta}. " : "") .
               "Track real-time: your-app-link";
    }

    /**
     * Technician arrived SMS
     */
    protected function technicianArrived(array $data): string
    {
        $technicianName = $data['technician_name'] ?? '';

        return "{$technicianName} has arrived at your location. Service will begin shortly.";
    }

    /**
     * Quote ready SMS
     */
    protected function quoteReady(array $data): string
    {
        $amount = $data['quote_amount'] ?? '';
        $currency = $data['currency'] ?? '₹';

        return "Your service quote is ready! " .
               ($amount ? "Total: {$currency}{$amount}. " : "") .
               "Review and approve at: your-app-link";
    }

    /**
     * Service complete SMS
     */
    protected function serviceComplete(array $data): string
    {
        $amount = $data['total_amount'] ?? '';
        $currency = $data['currency'] ?? '₹';

        return "Service completed successfully! " .
               ($amount ? "Total amount: {$currency}{$amount}. " : "") .
               "Please complete payment to close the ticket.";
    }

    /**
     * Payment reminder SMS
     */
    protected function paymentReminder(array $data): string
    {
        $amount = $data['amount_due'] ?? '';
        $currency = $data['currency'] ?? '₹';
        $daysOverdue = $data['days_overdue'] ?? '';

        return "Payment reminder: {$currency}{$amount} is " .
               ($daysOverdue ? "{$daysOverdue} days " : "") .
               "overdue. Pay now to avoid service interruption: your-app-link";
    }

    /**
     * Default SMS template
     */
    protected function defaultTemplate(array $data): string
    {
        $title = $data['title'] ?? '';
        $message = $data['message'] ?? '';

        return $title ? "{$title}: {$message}" : $message;
    }

    /**
     * Get data for SMS template from job/ticket
     *
     * @param Job|null $job
     * @param Ticket|null $ticket
     * @param User|null $user
     * @return array
     */
    public function prepareData(?Job $job = null, ?Ticket $ticket = null, ?User $user = null): array
    {
        $data = [];

        if ($ticket) {
            $data['ticket_id'] = $ticket->id;
            $data['device'] = $ticket->device;
            $data['scheduled_date'] = $ticket->preferred_date;
        }

        if ($job && $job->technician) {
            $data['technician_name'] = $job->technician->user->name ?? '';
            $data['technician_phone'] = $job->technician->user->phone ?? '';

            // Get ETA if available
            if ($job->estimated_duration_minutes && $job->distance_km) {
                $etaMinutes = $job->estimated_duration_minutes + ($job->distance_km * 2); // Rough estimate
                $data['eta'] = ceil($etaMinutes / 15) * 15 . ' mins'; // Round to nearest 15 mins
            }
        }

        // Add quote/job amounts if available
        if ($job && $job->quote) {
            $data['quote_amount'] = $job->quote->total_amount;
            $data['currency'] = '₹'; // Default to INR
        }

        if ($job && $job->payments) {
            $totalPaid = $job->payments->sum('amount');
            $data['amount_due'] = ($job->quote->total_amount ?? 0) - $totalPaid;
        }

        return $data;
    }
}