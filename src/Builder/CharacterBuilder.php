<?php

declare(strict_types=1);

namespace App\Builder;

use App\Armor\CanReduceAttack;
use App\Attack\CanPerformAttack;
use App\Attack\MultiAttack;
use App\Character\Character;

class CharacterBuilder
{
    private int $maxHealth;
    private int $baseDamage;
    private CanReduceAttack $armor;

    /** @var CanPerformAttack[] */
    private array $attacks;

    public static function new(): self
    {
        return new self();
    }

    public function setMaxHealth(int $maxHealth): self
    {
        $this->maxHealth = $maxHealth;

        return $this;
    }

    public function setBaseDamage(int $baseDamage): self
    {
        $this->baseDamage = $baseDamage;

        return $this;
    }

    public function setAttack(CanPerformAttack ...$attacks): self
    {
        $this->attacks = $attacks;

        return $this;
    }

    public function setArmor(CanReduceAttack $armor): self
    {
        $this->armor = $armor;

        return $this;
    }

    public function build(): Character
    {
        if (count($this->attacks) === 1) {
            $attack = $this->attacks[0];
        } else {
            $attack = new MultiAttack($this->attacks);
        }

        return new Character(
            $this->maxHealth,
            $this->baseDamage,
            $attack,
            $this->armor,
        );
    }
}
