<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyBudget extends Model
{
    protected $fillable = [
        'year',
        'month',
        'amount_bdt',
        'set_by',
        'notes',
    ];

    protected $casts = [
        'amount_bdt' => 'decimal:2',
    ];

    public function setter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }

    public function periodLabel(): string
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }
}
