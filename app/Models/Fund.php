<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'start_year', 'fund_manager_id'];

    protected $casts = [
        'start_year' => 'integer',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(FundManager::class, 'fund_manager_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(FundAlias::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }
}
