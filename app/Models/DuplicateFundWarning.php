<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateFundWarning extends Model
{
    protected $fillable = [
        'fund_id',
        'duplicate_fund_id',
        'matched_name',
        'fund_manager_id',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }

    public function duplicateFund(): BelongsTo
    {
        return $this->belongsTo(Fund::class, 'duplicate_fund_id');
    }

    public function fundManager(): BelongsTo
    {
        return $this->belongsTo(FundManager::class);
    }
}
