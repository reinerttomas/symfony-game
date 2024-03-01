<?php

declare(strict_types=1);

namespace App\Attack;

class MultiAttack implements CanPerformAttack
{
    /**
     * @param  CanPerformAttack[]  $attacks
     */
    public function __construct(private array $attacks)
    {
    }

    public function performAttack(int $baseDamage): int
    {
        $attack = $this->attacks[array_rand($this->attacks)];

        return $attack->performAttack($baseDamage);
    }
}
