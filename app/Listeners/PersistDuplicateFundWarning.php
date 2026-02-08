<?php

namespace App\Listeners;

use App\Events\DuplicateFundWarningEvent;
use App\Models\DuplicateFundWarning;
use Illuminate\Contracts\Queue\ShouldQueue;

class PersistDuplicateFundWarning implements ShouldQueue
{
    public function handle(DuplicateFundWarningEvent $event): void
    {
        DuplicateFundWarning::create([
            'fund_id' => $event->fundId,
            'duplicate_fund_id' => $event->duplicateFundId,
            'matched_name' => $event->matchedName,
            'fund_manager_id' => $event->fundManagerId,
        ]);
    }
}
