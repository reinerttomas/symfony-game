# Symfony Game - Design Patterns 

Welcome to the Symfony Game repository! This project is a simple game built with Symfony, where you can see practise examples of design patterns. This example is inspired by [symfonycasts](https://symfonycasts.com/screencast/design-patterns/strategy).

## Features

* ✅ Symfony 7
* ✅ PHPStan
* ✅ Laravel Pint (PHP Coding Standards Fixer)
* ✅ GitHub Actions
* 🚫 Tests

## Installation

Install dependencies using Composer

```
composer install
```

Create your .env file from example

```
cp .env.example .env
```

Start game by running command

```
php bin/console app:game:play
```

## Strategy Pattern

Technical definition

> The strategy pattern defines a family of algorithms, encapsulates each one and makes them interchangeable. It lets the algorithm vary independently from clients that use it.

In plain words

> The strategy pattern is a way to allow part of a class to be rewritten from the outside.

Game example

> We want to add special attack abilities for each character. For example, the mage will be able to cast spells.

In our example we have an attack interface and the implementation

```php
interface CanPerformAttack
{
    public function performAttack(int $baseDamage): int;
}

class Bow implements CanPerformAttack
{
    public function performAttack(int $baseDamage): int
    {
        return Dice::roll(100) > 70 ? $baseDamage * 3 : $baseDamage;
    }
}

class FireBolt implements CanPerformAttack
{
    public function performAttack(int $baseDamage): int
    {
        return Dice::roll(10) + Dice::roll(10) + Dice::roll(10);
    }
}

class TwoHandedSword implements CanPerformAttack
{
    public function performAttack(int $baseDamage): int
    {
        return $baseDamage + Dice::roll(12) + Dice::roll(12);
    }
}
```

We have one special attack when character can have more weapons

```php
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
```

And then we have our character with any weapon we want

```php
class Character
{
    private const int MAX_STAMINA = 100;
    private int $currentStamina = self::MAX_STAMINA;

    public function __construct(
        private int $maxHealth,
        private int $baseDamage,
        private readonly CanPerformAttack $attack,
        private readonly CanReduceAttack $armor,
    ) {
        $this->currentHealth = $this->maxHealth;
    }

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
}

class Game
{
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
}
```

## Builder Pattern

Technical definition

> A creational design pattern that lets you build and configure complex objects step-by-step.

In plain words

> the pattern allows you to produce different types and representations of an object using the same construction code.

Game example

> Our goal is to create characters easier and more clear. In the future we also want to make database queries. We can accomplish that by creating a builder class.

We have the builder class. Thanks to this solution we can still provide service by constructor

```php
class CharacterBuilder implements CanBuildCharacter
{
    private int $maxHealth;
    private int $baseDamage;
    private CanReduceAttack $armor;

    /** @var CanPerformAttack[] */
    private array $attacks;

    public function __construct(private LoggerInterface $logger)
    {
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
        $this->logger->info('Creating a character.', [
            'maxHealth' => $this->maxHealth,
            'baseDamage' => $this->baseDamage,
        ]);

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
```

And then it can be used as:

```php
$builder = (new CharacterBuilder($this->logger))
    ->setMaxHealth(75)
    ->setBaseDamage(9)
    ->setAttack(new FireBolt(), new Bow())
    ->setArmor(new Shield())
    ->build(),
```

## Factory

Technical definition

> factory is an object for creating other objects – formally a factory is a function or method that returns objects of a varying prototype or class from some method call, which is assumed to be "new".

In plain words

> Factory is just a class whose job is to create another class. It, like the builder pattern, is a creational pattern.

Game example

> In character builder we want to enable logging. We need to pass service to our builder. Thanks to factory we can easily do it. 

If we create interface for builder class we can provide different builders by business logic. Result its same, we can create character object.

```php
class CharacterBuilderFactory
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function createBuilder(): CanBuildCharacter
    {
        return new CharacterBuilder($this->logger);
    }
}
```

## Observer Pattern

Technical definition

> The observer pattern defines a one-to-many dependency between objects so that when one object changes state, all of its dependents are notified and updated automatically.

In plain words

> The observer pattern allows a bunch of objects to be notified by a central object when something happens.

Game example

> Each time you win a fight, your character will earn some XP. After you've earned enough points, the character will "level up", meaning it's base stats, like health and damage, will increase.

First we create class to earned xp each time we win the fight. This class need to be notified when fight finished.

```php
interface CanObserverFight
{
    public function onFightFinished(Fight $fight): void;
}

class XpEarnedObserver implements CanObserverFight
{
    public function __construct(private XpCalculatorInterface $xpCalculator)
    {
    }

    public function onFightFinished(Fight $fight): void
    {
        $this->xpCalculator->addXp($fight->getWinner(), $fight->getLoser()->getLevel());
    }
}
```

Next we need a way for every observer to subscribe to be notified when fight finished.

```php
class Game
{
    /** @var CanObserverFight[] */
    private array $observers = [];

    public function play(Character $player, Character $enemy): Fight
    {
        $fight = new Fight();

        while (true) {
            // player attacks

            if ($this->didPlayerDie($enemy)) {
                return $this->finishedFight($fight, $player, $enemy);
            }

            // enemy attacks

            if ($this->didPlayerDie($player)) {
                return $this->finishedFight($fight, $enemy, $player);
            }
        }
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
```

In Symfony we can autowire services in services.yaml. Simply after initialize Game, call the subscribe() method on it and pass, as an argument, the @App\Observer\XpEarnedObserver service.

```yaml
parameters:

    App\Game:
        calls:
            - subscribe: [ '@App\Observer\XpEarnedObserver' ]
```

## Publish-Subscriber

Technical definition

> It's more of a variation of the observer pattern.

In plain words

> With pub/sub, the observers (also called "listeners") tell the dispatcher which events they want to listen to. Then, the subject (whatever is doing the work) tells the dispatcher to dispatch the event. The dispatcher is then responsible for calling the listener methods.

Game example

> We want to run code before a fight starts.

```php
readonly class FightStartingEvent
{
    public function __construct(
        public Character $player,
        public Character $ai,
    ) {
    }
}

class OutputFightStartingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FightStartingEvent::class => 'onFightStart',
        ];
    }

    public function onFightStart(FightStartingEvent $event): void
    {
        $io = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
        $io->note('Fight is starting against: ' . $event->ai->getNickname());
    }
}

class Game
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function play(Character $player, Character $enemy): Fight
    {
        $this->eventDispatcher->dispatch(new FightStartingEvent($player, $enemy));

        // fight
    }
}
```

## Decorator Pattern

Technical definition

> The decorator pattern allows you to attach new behaviors to objects by placing these objects inside special wrapper objects that contain the behaviors.

In plain words

> The decorator pattern is like an intentional man-in-the-middle attack. You replace a class with your custom implementation, run some code, then call the true method.

Game example

> We want print some text into screen whenever a player levels up.

For the decorator pattern, there's one rule: the class that we want to decorate needs to implement an interface. If class were a vendor package and doesn't implement interface we can't use decorator.

```php
interface XpCalculatorInterface
{
    public function addXp(Character $winner, int $enemyLevel): void;
}

class XpCalculator implements XpCalculatorInterface
{
    public function addXp(Character $winner, int $enemyLevel): void
    {
        // logic
    }
}
```

We autowire XpCalculatorInterface to be alias for XpCalculator. 

```yaml
parameters:

    App\Service\XpCalculatorInterface:
        alias: App\Service\XpCalculator
```

Next step is create our decorator class. In Symfony we can use attribute and mark class as decorator for XpCalculatorInterface.

```php
#[AsDecorator(XpCalculatorInterface::class)]
class OutputtingXpCalculator implements XpCalculatorInterface
{
    public function __construct(
        private readonly XpCalculatorInterface $innerCalculator,
    ) {
    }

    public function addXp(Character $winner, int $enemyLevel): void
    {
        $beforeLevel = $winner->getLevel();

        $this->innerCalculator->addXp($winner, $enemyLevel);

        $afterLevel = $winner->getLevel();

        if ($beforeLevel !== $afterLevel) {
            $output = new ConsoleOutput();

            $output->writeln('--------------------------------');
            $output->writeln('<bg=green;fg=white>Congratulations! You\'ve leveled up!</>');
            $output->writeln(sprintf('You are now level "%d"', $winner->getLevel()));
            $output->writeln('--------------------------------');
        }
    }
}
```

## Decorator Pattern - core services

If we want to debug EventDispatcher we can use decorator. In our decorator we implements EventDispatcherInterface. We add our logic to dispatch method and rest methods is same. It's super easy. 

```php
#[AsDecorator('event_dispatcher')]
class DebugEventDispatcherDecorator implements EventDispatcherInterface
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        dump('--------------------');
        dump('Dispatching event: ' . $event::class);
        dump('--------------------');

        return $this->eventDispatcher->dispatch($event, $eventName);
    }

    /**
     * @param  callable|callable[]  $listener
     */
    public function addListener(string $eventName, callable|array $listener, int $priority = 0): void
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->eventDispatcher->addSubscriber($subscriber);
    }

    public function removeListener(string $eventName, callable $listener): void
    {
        $this->eventDispatcher->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->eventDispatcher->removeSubscriber($subscriber);
    }

    public function getListeners(?string $eventName = null): array
    {
        return $this->eventDispatcher->getListeners($eventName);
    }

    public function getListenerPriority(string $eventName, callable $listener): ?int
    {
        return $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(?string $eventName = null): bool
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }
```
