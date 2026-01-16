<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
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
        $totalAmount = $this->payment->amount + $this->payment->tip_amount;
        
        return (new MailMessage)
            ->subject("Payment Received - Job #{$this->payment->job_id}")
            ->line("Payment of " . \App\Helpers\CurrencyHelper::format($totalAmount) . " has been received.")
            ->line("Payment Method: " . ucfirst($this->payment->method))
            ->when($this->payment->tip_amount > 0, function ($mail) {
                return $mail->line("Tip Amount: " . \App\Helpers\CurrencyHelper::format($this->payment->tip_amount));
            })
            ->action('View Payment Details', url("/payments/{$this->payment->id}"))
            ->line('Thank you for your payment!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'job_id' => $this->payment->job_id,
            'amount' => $this->payment->amount,
            'tip_amount' => $this->payment->tip_amount,
            'total_amount' => $this->payment->amount + $this->payment->tip_amount,
            'method' => $this->payment->method,
            'status' => $this->payment->status,
            'message' => "Payment of " . \App\Helpers\CurrencyHelper::format($this->payment->amount + $this->payment->tip_amount) . " received via " . ucfirst($this->payment->method),
        ];
    }
}
