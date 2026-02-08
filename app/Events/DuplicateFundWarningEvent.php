<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DuplicateFundWarningEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $fundId,
        public int $duplicateFundId,
        public string $matchedName,
        public int $fundManagerId,
    ) {}
}
