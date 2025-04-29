<?php

namespace App\Strategies;

use App\Core\CommandInterface;
use App\Core\CommandQueue;
use App\Core\ExceptionStrategyInterface;
use App\Commands\RepeatTwiceCommand;
use App\Commands\LogCommand;
use App\Commands\RepeatCommand;
use Throwable;

/**
 * Поведение:
 *  ▸ Первая ошибка  → кладём RepeatTwiceCommand( attempt=2 )
 *  ▸ Вторая ошибка  → кладём RepeatTwiceCommand( attempt=3 )
 *  ▸ Третья ошибка  → кладём LogCommand
 */
class RepeatTwiceThenLogStrategy implements ExceptionStrategyInterface
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
        // если это не RepeatCommand → превращаем в RepeatTwiceCommand (первая попытка)
        if (!$cmd instanceof RepeatCommand) {
            $queue->add(new RepeatTwiceCommand($cmd, 1));
            return;
        }

        // если ещё есть попытки — повторяем
        if ($cmd->attemptsLeft() > 0) {
            $queue->add($cmd->nextAttempt());
            return;
        }

        // попыток не осталось → логируем
        $queue->add(new LogCommand($e, $cmd));
    }
}
