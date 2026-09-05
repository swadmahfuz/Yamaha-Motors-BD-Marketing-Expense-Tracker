<?php

namespace App\Notifications;

use App\Models\BudgetRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalNeededNotification extends Notification implements ShouldQueue
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
            ->subject('Approval needed: '.$this->budgetRequest->reference)
            ->line('A budget request requires your approval.')
            ->line('Reference: '.$this->budgetRequest->reference)
            ->line('Amount: BDT '.number_format((float) $this->budgetRequest->amount_bdt, 2))
            ->action('Review request', url('/requests/'.$this->budgetRequest->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_needed',
            'budget_request_id' => $this->budgetRequest->id,
            'reference' => $this->budgetRequest->reference,
            'amount_bdt' => $this->budgetRequest->amount_bdt,
            'message' => 'Approval needed for '.$this->budgetRequest->reference,
        ];
    }
}
