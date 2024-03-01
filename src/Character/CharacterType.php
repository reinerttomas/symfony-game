<?php

declare(strict_types=1);

namespace App\Character;

enum CharacterType: string
{
    case FIGHTER = 'fighter';
    case ARCHER = 'archer';
    case MAGE = 'mage';
    case MAGE_ARCHER = 'mage_archer';

    public function name(): string
    {
        return match ($this) {
            self::FIGHTER => 'Fighter',
            self::ARCHER => 'Archer',
            self::MAGE => 'Mage',
            self::MAGE_ARCHER => 'Mage Archer',
        };
    }

    /**
     * @return array<string>
     */
    public static function choices(): array
    {
        $choices = [];

        foreach (self::cases() as $type) {
            $choices[] = $type->value;
        }

        return $choices;
    }
}
