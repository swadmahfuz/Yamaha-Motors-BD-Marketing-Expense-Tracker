<?php

namespace App\Enums;

enum BudgetRequestStatus: string
{
    case Draft = 'draft';
    case AwaitingSuperAdmin = 'awaiting_super_admin';
    case InApproval = 'in_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case InProgress = 'in_progress';
    case PartiallyReported = 'partially_reported';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::AwaitingSuperAdmin => 'Awaiting Super Admin',
            self::InApproval => 'In Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::InProgress => 'In Progress',
            self::PartiallyReported => 'Partially Reported',
            self::Closed => 'Closed',
        };
    }

    public static function committedStatuses(): array
    {
        return [
            self::Approved->value,
            self::InProgress->value,
            self::PartiallyReported->value,
        ];
    }
}
