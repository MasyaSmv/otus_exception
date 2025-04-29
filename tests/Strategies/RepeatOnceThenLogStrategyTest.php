<?php

namespace Tests\Strategies;

use App\Core\CommandInterface;
use App\Core\CommandProcessor;
use App\Core\CommandQueue;
use App\Core\ExceptionStrategyRegistry;
use App\Strategies\RepeatOnceThenLogStrategy;
use RuntimeException;
use Tests\BaseTestCase;
use Throwable;

class RepeatOnceThenLogStrategyTest extends BaseTestCase
{
    /**
     * @return void
     * @throws Throwable
     */
    public function testRepeatThenLog(): void
    {
        // 1. регистрируем стратегию
        ExceptionStrategyRegistry::instance()->register('*', '*', new RepeatOnceThenLogStrategy());

        // 2. создаём очередь и тестовую «падающую» команду
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

        // запуск №1: упали → стратегия положила RepeatCommand
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        // запуск №2: повтор; снова исключение → стратегия положила LogCommand
        $processor->runOnce();
        $this->assertFalse($queue->isEmpty());

        // запуск №3: отрабатывает LogCommand и очередь пустеет
        $processor->runOnce();
        $this->assertTrue($queue->isEmpty());

        // убедимся, что execute() действительно вызывался 2 раза
        $this->assertEquals(2, $cmd->calls);
    }
}
