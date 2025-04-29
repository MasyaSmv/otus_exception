<?php
namespace Tests\Core;

use App\Core\CommandInterface;
use App\Core\CommandProcessor;
use App\Core\CommandQueue;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Покрываем две оставшиеся ветки:
 *  1) очередь пуста – runOnce должен просто выйти;
 *  2) стратегия не найдена – исключение пробрасывается.
 */
class CommandProcessorBranchesTest extends TestCase
{
    /**
     * @return void
     * @throws Throwable
     */
    public function testRunOnceOnEmptyQueueDoesNothing(): void
    {
        $queue = new CommandQueue();
        $processor = new CommandProcessor($queue);

        // если метод отработает без throw – ветка покрыта
        $processor->runOnce();
        $this->assertTrue($queue->isEmpty());
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRunOnceThrowsWhenNoStrategy(): void
    {
        $queue = new CommandQueue();

        // команда бросает исключение,
        // а реестр стратегий (обнулён BaseTestCase::tearDown) пуст
        $queue->add(new class implements CommandInterface {
            public function execute(): void
            {
                throw new RuntimeException('no strategy');
            }
        });

        $this->expectException(RuntimeException::class);
        (new CommandProcessor($queue))->runOnce();
    }
}
