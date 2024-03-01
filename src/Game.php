<?php

declare(strict_types=1);

namespace App;

use App\Armor\IceBlock;
use App\Armor\Leather;
use App\Armor\Shield;
use App\Attack\Bow;
use App\Attack\FireBolt;
use App\Attack\MultiAttack;
use App\Attack\TwoHandedSword;
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
            CharacterType::FIGHTER => new Character(90, 12, new TwoHandedSword(), new Shield()),
            CharacterType::ARCHER => new Character(80, 10, new Bow(), new Leather()),
            CharacterType::MAGE => new Character(70, 8, new FireBolt(), new IceBlock()),
            CharacterType::MAGE_ARCHER => new Character(75, 9, new MultiAttack([new FireBolt(), new Bow()]), new Shield()),
        };
    }

    private function didPlayerDie(Character $player): bool
    {
        return $player->getCurrentHealth() <= 0;
    }
}
