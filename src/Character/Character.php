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
    private int $level = 1;
    private int $xp = 0;

    public function __construct(
        private int $maxHealth,
        private int $baseDamage,
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

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function addXp(int $xpEarned): int
    {
        $this->xp += $xpEarned;

        return $this->xp;
    }

    public function levelUp(): void
    {
        // +15% bonus to stats
        $bonus = 1.15;

        $this->level++;
        $this->maxHealth = (int) floor($this->maxHealth * $bonus);
        $this->baseDamage = (int) floor($this->baseDamage * $bonus);

        // todo: level up attack and armor type
    }

    public function rest(): void
    {
        // Restore player's health before next fight.
        $this->currentHealth = $this->maxHealth;
        $this->currentStamina = self::MAX_STAMINA;
    }
}
