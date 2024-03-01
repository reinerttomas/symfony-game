<?php

declare(strict_types=1);

namespace App\Armor;

use App\Dice;
use Random\RandomException;

class Shield implements CanReduceAttack
{
    /**
     * @throws RandomException
     */
    public function getArmorReduction(int $damage): int
    {
        return Dice::roll(100) > 80 ? $damage : 0;
    }
}
