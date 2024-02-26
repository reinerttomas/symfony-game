<?php

declare(strict_types=1);

namespace App;

use Random\RandomException;

class Dice
{
    /**
     * @throws RandomException
     */
    public static function roll(int $sides): int
    {
        return random_int(1, $sides);
    }
}
