<?php

declare(strict_types=1);

namespace App\Armor;

interface CanReduceAttack
{
    public function getArmorReduction(int $damage): int;
}
