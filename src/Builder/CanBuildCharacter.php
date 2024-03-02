<?php

declare(strict_types=1);

namespace App\Builder;

use App\Armor\CanReduceAttack;
use App\Attack\CanPerformAttack;
use App\Character\Character;

interface CanBuildCharacter
{
    public function setMaxHealth(int $maxHealth): self;

    public function setBaseDamage(int $baseDamage): self;

    public function setAttack(CanPerformAttack ...$attacks): self;

    public function setArmor(CanReduceAttack $armor): self;

    public function build(): Character;
}
