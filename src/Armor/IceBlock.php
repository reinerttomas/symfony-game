<?php

declare(strict_types=1);

namespace App\Armor;

use App\Dice;
use Random\RandomException;

class IceBlock implements CanReduceAttack
{
    /**
     * @throws RandomException
     */
    public function getArmorReduction(int $damage): int
    {
        return Dice::roll(8) + Dice::roll(8);
    }
}
