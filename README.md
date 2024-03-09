# Symfony Game - Design Patterns 

Welcome to the Symfony Game repository! This project is a simple game built with Symfony, where you can see practise examples of design patterns. This example is inspired by [symfonycasts](https://symfonycasts.com/screencast/design-patterns/strategy).

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

If we create interface for builder class we can provide different builders by business logic. Result its same, we can create character object. Thanks to dependency injection we can get any services from container-

```php
class CharacterBuilderFactory
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function createBuilder(): CanBuildCharacter
    {
        if (Dice::roll(100) > 90) {
            return new CharacterGreaterHealthBuilder();
        }

        return new CharacterBuilder($this->logger);
    }
}
```
