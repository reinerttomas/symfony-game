<?php

declare(strict_types=1);

namespace App\Service;

use App\Character\Character;

interface XpCalculatorInterface
{
    public function addXp(Character $winner, int $enemyLevel): void;
}
