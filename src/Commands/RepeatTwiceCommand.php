<?php

namespace App\Commands;

use App\Core\CommandInterface;

readonly class RepeatTwiceCommand extends RepeatCommand
{
    public function __construct(CommandInterface $origin, int $attempt = 1)
    {
        parent::__construct($origin, 2, $attempt);
    }
}
