<?php

namespace Tests\Core;

use App\Core\CommandInterface;
use App\Core\CommandProcessor;
use App\Core\CommandQueue;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

class CommandProcessorNoStrategyTest extends TestCase
{
    /**
     * @return void
     * @throws Throwable
     */
    public function testThrowsWhenNoStrategyFound(): void
    {
        $this->expectException(RuntimeException::class);

        $q = new CommandQueue();
        $q->add(new class implements CommandInterface {
            public function execute(): void
            {
                throw new RuntimeException('no strategy');
            }
        });

        (new CommandProcessor($q))->runOnce();
    }
}
