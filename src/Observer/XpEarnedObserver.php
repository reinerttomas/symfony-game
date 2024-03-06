<?php

declare(strict_types=1);

namespace App\Observer;

use App\Fight;
use App\Service\XpCalculator;

readonly class XpEarnedObserver implements CanObserverFight
{
    public function __construct(private XpCalculator $xpCalculator)
    {
    }

    public function onFightFinished(Fight $fight): void
    {
        $this->xpCalculator->addXp($fight->getWinner(), $fight->getLoser()->getLevel());
    }
}
