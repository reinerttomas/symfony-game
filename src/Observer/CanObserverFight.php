<?php

declare(strict_types=1);

namespace App\Observer;

use App\Fight;

interface CanObserverFight
{
    public function onFightFinished(Fight $fight): void;
}
