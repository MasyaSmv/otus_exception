<?php

namespace Tests\Strategies;

use App\Core\CommandInterface;
use App\Core\CommandProcessor;
use App\Core\CommandQueue;
use App\Core\ExceptionStrategyRegistry;
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
        // регистрируем стратегию только для этого теста
        ExceptionStrategyRegistry::instance()->register('*', '*', new RepeatTwiceThenLogStrategy());

        $queue = new CommandQueue();

        $cmd = new class implements CommandInterface {
            public int $calls = 0;
            public function execute(): void
            {
                $this->calls++;
                throw new RuntimeException('boom');
            }
        };

        $queue->add($cmd);
        $processor = new CommandProcessor($queue);

        // 1-я попытка  → добавится first RepeatTwiceCommand
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        // 2-я попытка  → добавится second RepeatTwiceCommand
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        // 3-я попытка  → добавится LogCommand
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        // LogCommand выполняется
        $processor->runOnce();
        $this->assertTrue($queue->isEmpty());

        // убеждаемся, что execute() вызвали ровно 3 раза
        $this->assertSame(3, $cmd->calls);
    }
}
