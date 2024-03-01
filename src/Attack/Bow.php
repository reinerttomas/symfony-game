<?php

declare(strict_types=1);

namespace App\Attack;

use App\Dice;
use Random\RandomException;

class Bow implements CanPerformAttack
{
    /**
     * @throws RandomException
     */
    public function performAttack(int $baseDamage): int
    {
        return Dice::roll(100) > 70 ? $baseDamage * 3 : $baseDamage;
    }
}
