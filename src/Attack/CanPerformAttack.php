<?php

declare(strict_types=1);

namespace App\Attack;

interface CanPerformAttack
{
    public function performAttack(int $baseDamage): int;
}
