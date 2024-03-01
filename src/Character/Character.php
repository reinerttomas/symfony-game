<?php

declare(strict_types=1);

namespace App\Character;

use App\Armor\CanReduceAttack;
use App\Attack\CanPerformAttack;
use App\Dice;
use Random\RandomException;

class Character
{
    private const int MAX_STAMINA = 100;
    private int $currentStamina = self::MAX_STAMINA;
    private int $currentHealth;
    private string $nickname = '';

    public function __construct(
        private readonly int $maxHealth,
        private readonly int $baseDamage,
        private readonly CanPerformAttack $attack,
        private readonly CanReduceAttack $armor,
    ) {
        $this->currentHealth = $this->maxHealth;
    }

    /**
     * @throws RandomException
     */
    public function attack(): int
    {
        $this->currentStamina -= (25 + Dice::roll(20));

        // can't attack this turn
        if ($this->currentStamina <= 0) {
            $this->currentStamina = self::MAX_STAMINA;

            return 0;
        }

        return $this->attack->performAttack($this->baseDamage);
    }

    public function receiveAttack(int $damage): int
    {
        $armorReduction = $this->armor->getArmorReduction($damage);
        $damageTaken = max($damage - $armorReduction, 0);
        $this->currentHealth -= $damageTaken;

        return $damageTaken;
    }

    public function getCurrentHealth(): int
    {
        return $this->currentHealth;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function rest(): void
    {
        // Restore player's health before next fight.
        $this->currentHealth = $this->maxHealth;
        $this->currentStamina = self::MAX_STAMINA;
    }
}
