<?php

declare(strict_types=1);

namespace App\Builder;

use App\Dice;
use Psr\Log\LoggerInterface;
use Random\RandomException;

readonly class CharacterBuilderFactory
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @throws RandomException
     */
    public function createBuilder(): CanBuildCharacter
    {
        if (Dice::roll(100) > 90) {
            return new CharacterGreaterHealthBuilder();
        }

        return new CharacterBuilder($this->logger);
    }
}
