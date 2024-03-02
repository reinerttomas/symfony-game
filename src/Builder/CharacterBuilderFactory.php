<?php

declare(strict_types=1);

namespace App\Builder;

use Psr\Log\LoggerInterface;

readonly class CharacterBuilderFactory
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function createBuilder(): CharacterBuilder
    {
        return new CharacterBuilder($this->logger);
    }
}
