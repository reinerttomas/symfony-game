<?php

declare(strict_types=1);

namespace App\Attack;

use App\Dice;
use Random\RandomException;

class TwoHandedSword implements CanPerformAttack
{
    /**
     * @throws RandomException
     */
    public function performAttack(int $baseDamage): int
    {
        return $baseDamage + Dice::roll(12) + Dice::roll(12);
    }
}
