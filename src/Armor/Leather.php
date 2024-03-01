<?php

declare(strict_types=1);

namespace App\Armor;

class Leather implements CanReduceAttack
{
    public function getArmorReduction(int $damage): int
    {
        return (int) floor($damage * 0.25);
    }
}
