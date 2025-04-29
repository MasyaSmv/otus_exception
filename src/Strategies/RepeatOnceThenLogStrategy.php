<?php

namespace App\Strategies;

use App\Core\CommandInterface;
use App\Core\CommandQueue;
use App\Core\ExceptionStrategyInterface;
use App\Commands\LogCommand;
use App\Commands\RepeatCommand;
use Throwable;

class RepeatOnceThenLogStrategy implements ExceptionStrategyInterface
{
    /**
     * @param CommandInterface $cmd
     * @param Throwable $e
     * @param CommandQueue $queue
     *
     * @return void
     */
    public function handle(CommandInterface $cmd, Throwable $e, CommandQueue $queue): void
    {
        // Если это уже повторная попытка – логируем
        if ($cmd instanceof RepeatCommand && $cmd->attemptsLeft() === 0) {
            $queue->add(new LogCommand($e, $cmd));
            return;
        }

        // Иначе кладём команду-повторитель (одна попытка)
        $queue->add(new RepeatCommand($cmd, 1));
    }
}
