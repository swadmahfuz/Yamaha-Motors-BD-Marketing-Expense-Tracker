<?php

namespace App\Notifications;

use App\Models\BudgetRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestOutcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BudgetRequest $budgetRequest,
        public string $outcome,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(ucfirst($this->outcome).': '.$this->budgetRequest->reference)
            ->line('Budget request '.$this->budgetRequest->reference.' was '.$this->outcome.'.');

        if ($this->reason) {
            $mail->line('Reason: '.$this->reason);
        }

        return $mail->action('View request', url('/requests/'.$this->budgetRequest->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'request_outcome',
            'outcome' => $this->outcome,
            'budget_request_id' => $this->budgetRequest->id,
            'reference' => $this->budgetRequest->reference,
            'reason' => $this->reason,
            'message' => 'Request '.$this->budgetRequest->reference.' was '.$this->outcome,
        ];
    }
}
