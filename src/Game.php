<?php

declare(strict_types=1);

namespace App;

use App\Armor\IceBlock;
use App\Armor\Leather;
use App\Armor\Shield;
use App\Attack\Bow;
use App\Attack\FireBolt;
use App\Attack\TwoHandedSword;
use App\Builder\CharacterBuilder;
use App\Builder\CharacterBuilderFactory;
use App\Character\Character;
use App\Character\CharacterType;

readonly class Game
{
    public function __construct(
        private CharacterBuilderFactory $characterBuilderFactory,
    ) {
    }

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
            CharacterType::FIGHTER => $this->characterBuilderFactory->createBuilder()
                ->setMaxHealth(90)
                ->setBaseDamage(12)
                ->setAttack(new TwoHandedSword())
                ->setArmor(new Shield())
                ->build(),
            CharacterType::ARCHER => $this->characterBuilderFactory->createBuilder()
                ->setMaxHealth(80)
                ->setBaseDamage(10)
                ->setAttack(new Bow())
                ->setArmor(new Leather())
                ->build(),
            CharacterType::MAGE => $this->characterBuilderFactory->createBuilder()
                ->setMaxHealth(70)
                ->setBaseDamage(8)
                ->setAttack(new FireBolt())
                ->setArmor(new IceBlock())
                ->build(),
            CharacterType::MAGE_ARCHER => $this->characterBuilderFactory->createBuilder()
                ->setMaxHealth(75)
                ->setBaseDamage(9)
                ->setAttack(new FireBolt(), new Bow())
                ->setArmor(new Shield())
                ->build(),
        };
    }

    private function didPlayerDie(Character $player): bool
    {
        return $player->getCurrentHealth() <= 0;
    }
}
