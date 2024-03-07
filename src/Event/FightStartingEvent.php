<?php

declare(strict_types=1);

namespace App\Event;

use App\Character\Character;

readonly class FightStartingEvent
{
    public function __construct(
        public Character $player,
        public Character $ai,
    ) {
    }
}
