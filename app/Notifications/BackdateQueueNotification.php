<?php

namespace App\Notifications;

use App\Models\BudgetRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackdateQueueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BudgetRequest $budgetRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Backdated request awaiting clearance: '.$this->budgetRequest->reference)
            ->line('A backdated budget request needs Super Admin clearance.')
            ->line('Request date: '.$this->budgetRequest->request_date->format('Y-m-d'))
            ->action('Review backdate queue', url('/super-admin/backdates'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'backdate_queue',
            'budget_request_id' => $this->budgetRequest->id,
            'reference' => $this->budgetRequest->reference,
            'message' => 'Backdated request '.$this->budgetRequest->reference.' awaits clearance',
        ];
    }
}
