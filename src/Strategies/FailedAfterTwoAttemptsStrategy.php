<?php

namespace App\Strategies;

use App\Core\CommandInterface;
use App\Core\CommandQueue;
use App\Core\ExceptionStrategyInterface;
use App\Commands\FailedAfterTwoAttemptsCommand;
use App\Commands\LogCommand;
use Throwable;

/**
 * Просто пишет ошибку в лог, получив маркер-команду.
 */
class FailedAfterTwoAttemptsStrategy implements ExceptionStrategyInterface
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
        if ($cmd instanceof FailedAfterTwoAttemptsCommand) {
            $queue->add(new LogCommand($cmd->exception(), $cmd->origin()));
        }
    }
}
