<?php

namespace Tests\Strategies;

use App\Commands\FailedAfterTwoAttemptsCommand;
use App\Commands\LogCommand;
use App\Core\CommandInterface;
use App\Core\CommandProcessor;
use App\Core\CommandQueue;
use App\Core\ExceptionStrategyRegistry;
use App\Strategies\FailedAfterTwoAttemptsStrategy;
use App\Strategies\RepeatTwiceThenLogStrategy;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

class RepeatTwiceThenLogStrategyTest extends TestCase
{
    /**
     * @return void
     * @throws Throwable
     */
    public function testRepeatTwiceThenLog(): void
    {
        /** Заново настраиваем реестр, чтобы быть независимыми от других тестов */
        $reg = ExceptionStrategyRegistry::instance();
        $reg->register('*', '*', new RepeatTwiceThenLogStrategy());
        $reg->register(                       // ←--- стратегия для маркера
            FailedAfterTwoAttemptsCommand::class,
            '*',
            new FailedAfterTwoAttemptsStrategy()
        );
        
        $queue = new CommandQueue();

        $cmd = new class implements CommandInterface {
            public int $calls = 0;
            public function execute(): void
            {
                $this->calls++;
                throw new RuntimeException('fail');
            }
        };

        $queue->add($cmd);
        $processor = new CommandProcessor($queue);

        // 1-я попытка → RepeatTwiceCommand
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        // 2-я попытка → RepeatTwiceCommand (attempt 2)
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        // 3-я попытка → FailedAfterTwoAttemptsCommand
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        $next = $queue->take();
        $this->assertInstanceOf(FailedAfterTwoAttemptsCommand::class, $next);
        $this->assertEquals(3, $cmd->calls);

        // Важно: выполнить маркер-команду, чтобы вызвалась стратегия
        $queue->add($next);
        $processor->runOnce();

        // Теперь очередь должна содержать LogCommand
        $this->assertFalse($queue->isEmpty());
        $this->assertInstanceOf(LogCommand::class, $queue->take());

    }

}
