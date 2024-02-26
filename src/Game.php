<?php

declare(strict_types=1);

namespace App;

use App\Character\Character;
use App\Character\CharacterType;

class Game
{
    public function play(Character $player, Character $enemy): Fight
    {
        $player->rest();

        $fight = new Fight();

        while (true) {
            $fight->addRound();
            $damage = $player->attack();

            if ($damage === 0) {
                $fight->addExhaustedTurn();
            }

            $damageDealt = $enemy->receiveAttack($damage);
            $fight->addDamageDealt($damageDealt);

            if ($this->didPlayerDie($enemy)) {
                $fight->setWinner($player);
                $fight->setLoser($enemy);

                return $fight;
            }

            $damageReceived = $player->receiveAttack($enemy->attack());
            $fight->addDamageReceived($damageReceived);

            if ($this->didPlayerDie($player)) {
                $fight->setWinner($enemy);
                $fight->setLoser($player);

                return $fight;
            }
        }
    }

    public function createCharacter(CharacterType $type): Character
    {
        return match ($type) {
            CharacterType::FIGHTER => new Character(90, 12, 0.25),
            CharacterType::ARCHER => new Character(80, 10, 0.15),
            CharacterType::MAGE => new Character(70, 8, 0.10),
        };
    }

    private function didPlayerDie(Character $player): bool
    {
        return $player->getCurrentHealth() <= 0;
    }
}
