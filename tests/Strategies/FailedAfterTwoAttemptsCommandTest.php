<?php

namespace Tests\Strategies;

use App\Commands\FailedAfterTwoAttemptsCommand;
use App\Commands\LogCommand;
use App\Core\CommandInterface;
use App\Core\CommandProcessor;
use App\Core\CommandQueue;
use RuntimeException;
use Tests\BaseTestCase;
use Throwable;

class FailedAfterTwoAttemptsCommandTest extends BaseTestCase
{
    /**
     * @return void
     * @throws Throwable
     */
    public function testMarkerAppearsAfterTwoFailures(): void
    {
        $queue = new CommandQueue();

        $cmd = new class implements CommandInterface {
            public int $calls = 0;

            public function execute(): void
            {
                $this->calls++;
                throw new RuntimeException('oops');
            }
        };

        $queue->add($cmd);
        $processor = new CommandProcessor($queue);

        // три запуска → две попытки + маркер-команда
        $processor->runOnce(); // первая
        $processor->runOnce(); // вторая
        $processor->runOnce(); // кладётся FailedAfterTwoAttemptsCommand

        /** @var CommandInterface $next */
        $next = $queue->take();
        $this->assertInstanceOf(FailedAfterTwoAttemptsCommand::class, $next);

        // убедимся, что execute() вызвали 3 раза
        $this->assertEquals(3, $cmd->calls);

        // проверяем, что дальше в очередь ушёл LogCommand (стратегия по умолчанию)
        $queue->add($next);           // вернём маркер и выполним
        $processor->runOnce();        // выполнится стратегия → LogCommand

        $this->assertFalse($queue->isEmpty());
        $this->assertInstanceOf(LogCommand::class, $queue->take());
    }
}
