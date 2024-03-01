<?php

declare(strict_types=1);

namespace App\Attack;

use App\Dice;
use Random\RandomException;

class FireBolt implements CanPerformAttack
{
    /**
     * @throws RandomException
     */
    public function performAttack(int $baseDamage): int
    {
        return Dice::roll(10) + Dice::roll(10) + Dice::roll(10);
    }
}
