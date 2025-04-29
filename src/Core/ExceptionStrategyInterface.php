<?php

namespace App\Core;

use Throwable;

interface ExceptionStrategyInterface
{
    /**
     * Реализует реакцию на исключение, возникшее в $cmd.
     * Чаще всего – кладёт новые команды обратно в очередь.
     */
    public function handle(CommandInterface $cmd, Throwable $e, CommandQueue $queue): void;
}
