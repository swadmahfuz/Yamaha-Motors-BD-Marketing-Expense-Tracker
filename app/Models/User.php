<?php

namespace App\Models;

use App\Enums\BudgetRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'team_id',
        'manager_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function initiatedRequests(): HasMany
    {
        return $this->hasMany(BudgetRequest::class, 'initiator_id');
    }

    public function spenderRequests(): HasMany
    {
        return $this->hasMany(BudgetRequest::class, 'spender_id');
    }

    public function isFinalApprover(): bool
    {
        return $this->hasRole('head_of_marketing') || $this->manager_id === null;
    }

    /**
     * Approvers who would receive a newly submitted request for this spender,
     * matching ApprovalService::buildApprovalChain (manager → … → final).
     */
    public function approvalChainUsers(): Collection
    {
        $approvers = collect();
        $current = $this->manager;
        $guard = 0;

        while ($current && $guard < 20) {
            $approvers->push($current);

            if ($current->isFinalApprover()) {
                break;
            }

            $current = $current->manager;
            $guard++;
        }

        if ($approvers->isEmpty()) {
            $hom = static::role('head_of_marketing')->first();
            if ($hom) {
                $approvers->push($hom);
            }
        }

        return $approvers;
    }

    public function canInitiate(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->hasRole('staff')
            || $this->hasRole('initiator')
            || $this->hasRole('spender')
            || $this->hasAnyRole(['head_of_marketing', 'super_admin', 'admin']);
    }

    public function canAppearAsSpender(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->hasRole('staff')
            || $this->hasRole('spender')
            || $this->hasRole('initiator')
            || $this->hasAnyRole(['head_of_marketing', 'super_admin', 'admin']);
    }

    public function canAccessApprovals(): bool
    {
        if ($this->hasAnyRole(['head_of_marketing', 'admin', 'super_admin', 'approver'])) {
            return true;
        }

        if ($this->directReports()->exists()) {
            return true;
        }

        return ApprovalStep::query()
            ->where('approver_id', $this->id)
            ->where('status', 'pending')
            ->whereHas('budgetRequest', function ($query) {
                $query->where('status', BudgetRequestStatus::InApproval)
                    ->whereColumn('budget_requests.current_approval_step', 'approval_steps.step_order');
            })
            ->exists();
    }

    public function scopeSelectableAsSpender(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->role(['staff', 'spender', 'initiator', 'head_of_marketing', 'super_admin', 'admin']);
    }
}
