<?php

namespace Tests\Core;

use App\Core\CommandInterface;
use App\Core\CommandProcessor;
use App\Core\CommandQueue;
use Tests\BaseTestCase;
use Throwable;

class CommandProcessorTest extends BaseTestCase
{
    /**
     * @return void
     * @throws Throwable
     */
    public function testCommandExecuted(): void
    {
        $queue = new CommandQueue();

        $flag = false;
        
        $cmd = new class($flag) implements CommandInterface {
            public function __construct(private bool &$flag) {}
            public function execute(): void
            {
                $this->flag = true;
            }
        };

        $queue->add($cmd);
        $processor = new CommandProcessor($queue);
        $processor->runOnce();

        $this->assertTrue($flag);
        $this->assertTrue($queue->isEmpty());
    }
}
