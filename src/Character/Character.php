<?php

declare(strict_types=1);

namespace App\Character;

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
        private readonly float $armor
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

        return $this->baseDamage + Dice::roll(6);
    }

    public function receiveAttack(int $damage): int
    {
        $armorReduction = (int) ($this->armor * $damage);
        $damageTaken = $damage - $armorReduction;
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
