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
