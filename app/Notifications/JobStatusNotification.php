<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class JobStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Job $job;
    protected string $status;
    protected string $message;

    public function __construct(Job $job, string $status, string $message = '')
    {
        $this->job = $job;
        $this->status = $status;
        $this->message = $message ?: $this->getDefaultMessage($status);
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        
        // Add mail if user has email
        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Job #{$this->job->id} - {$this->status}")
            ->line($this->message)
            ->action('View Job', url("/jobs/{$this->job->id}"))
            ->line('Thank you for using our service!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'status' => $this->status,
            'message' => $this->message,
            'ticket_id' => $this->job->ticket_id,
            'device' => $this->job->ticket->device->brand . ' ' . $this->job->ticket->device->device_type,
        ];
    }

    protected function getDefaultMessage(string $status): string
    {
        return match($status) {
            'accepted' => "Job #{$this->job->id} has been accepted by a technician.",
            'en_route' => "Technician is on the way for Job #{$this->job->id}.",
            'arrived' => "Technician has arrived for Job #{$this->job->id}.",
            'diagnosing' => "Technician is diagnosing the issue for Job #{$this->job->id}.",
            'repairing' => "Repair work has started for Job #{$this->job->id}.",
            'completed' => "Job #{$this->job->id} has been completed successfully.",
            default => "Job #{$this->job->id} status updated to {$status}.",
        };
    }
}
