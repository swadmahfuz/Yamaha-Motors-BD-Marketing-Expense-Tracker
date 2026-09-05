<?php

namespace App\Models;

use App\Enums\BudgetRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BudgetRequest extends Model
{
    protected $fillable = [
        'reference',
        'initiator_id',
        'spender_id',
        'team_id',
        'category_id',
        'objective',
        'description',
        'expected_outcome',
        'amount_bdt',
        'approved_amount_bdt',
        'request_date',
        'budget_year',
        'budget_month',
        'is_backdated',
        'backdate_reason',
        'backdate_evidence',
        'activity_start_date',
        'activity_end_date',
        'location',
        'vendor',
        'internal_notes',
        'status',
        'current_approval_step',
        'submitted_at',
        'approved_at',
        'closed_at',
        'super_admin_cleared_by',
        'super_admin_cleared_at',
        'super_admin_comment',
    ];

    protected $casts = [
        'amount_bdt' => 'decimal:2',
        'approved_amount_bdt' => 'decimal:2',
        'request_date' => 'date',
        'activity_start_date' => 'date',
        'activity_end_date' => 'date',
        'is_backdated' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
        'super_admin_cleared_at' => 'datetime',
        'status' => BudgetRequestStatus::class,
    ];

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function spender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spender_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }

    public function actualExpenses(): HasMany
    {
        return $this->hasMany(ActualExpense::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function superAdminClearer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_cleared_by');
    }

    public function totalActualAmount(): float
    {
        return (float) $this->actualExpenses()->sum('amount_bdt');
    }

    public function remainingCommitment(): float
    {
        if (! in_array($this->status->value, BudgetRequestStatus::committedStatuses(), true)) {
            return 0;
        }

        $approved = (float) ($this->approved_amount_bdt ?? $this->amount_bdt);

        return max(0, $approved - $this->totalActualAmount());
    }

    public function isOverrun(): bool
    {
        $approved = (float) ($this->approved_amount_bdt ?? $this->amount_bdt);

        return $this->totalActualAmount() > $approved;
    }

    public function varianceAmount(): float
    {
        $approved = (float) ($this->approved_amount_bdt ?? $this->amount_bdt);

        return $this->totalActualAmount() - $approved;
    }

    public function budgetPeriodLabel(): string
    {
        return sprintf('%04d-%02d', $this->budget_year, $this->budget_month);
    }
}
