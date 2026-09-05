<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ActualExpense extends Model
{
    protected $fillable = [
        'budget_request_id',
        'reported_by',
        'amount_bdt',
        'spend_date',
        'vendor',
        'description',
        'overrun_justification',
    ];

    protected $casts = [
        'amount_bdt' => 'decimal:2',
        'spend_date' => 'date',
    ];

    public function budgetRequest(): BelongsTo
    {
        return $this->belongsTo(BudgetRequest::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
