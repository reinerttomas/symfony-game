<?php

declare(strict_types=1);

namespace App;

use App\Armor\IceBlock;
use App\Armor\Leather;
use App\Armor\Shield;
use App\Attack\Bow;
use App\Attack\FireBolt;
use App\Attack\TwoHandedSword;
use App\Builder\CharacterBuilderFactory;
use App\Character\Character;
use App\Character\CharacterType;
use App\Event\FightStartingEvent;
use App\Observer\CanObserverFight;
use Random\RandomException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Game
{
    /** @var CanObserverFight[] */
    private array $observers = [];

    public function __construct(
        private CharacterBuilderFactory $characterBuilderFactory,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function play(Character $player, Character $enemy): Fight
    {
        $this->eventDispatcher->dispatch(new FightStartingEvent($player, $enemy));

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
                return $this->finishedFight($fight, $player, $enemy);
            }

            $damageReceived = $player->receiveAttack($enemy->attack());
            $fight->addDamageReceived($damageReceived);

            if ($this->didPlayerDie($player)) {
                return $this->finishedFight($fight, $enemy, $player);
            }
        }
    }

    /**
     * @throws RandomException
     */
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

    public function subscribe(CanObserverFight $observer): void
    {
        if (! in_array($observer, $this->observers, true)) {
            $this->observers[] = $observer;
        }
    }

    public function unsubscribe(CanObserverFight $canObserverFight): void
    {
        $key = array_search($canObserverFight, $this->observers, true);

        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }

    private function didPlayerDie(Character $player): bool
    {
        return $player->getCurrentHealth() <= 0;
    }

    public function finishedFight(Fight $fight, Character $winner, Character $loser): Fight
    {
        $fight->setWinner($winner);
        $fight->setLoser($loser);

        $this->notify($fight);

        return $fight;
    }

    private function notify(Fight $fight): void
    {
        foreach ($this->observers as $observer) {
            $observer->onFightFinished($fight);
        }
    }
}
