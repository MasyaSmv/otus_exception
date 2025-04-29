<?php

namespace Tests\Core;

use App\Core\CommandQueue;
use Tests\BaseTestCase;

class CommandQueueTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testTakeOnEmptyQueueReturnsNull(): void
    {
        $q = new CommandQueue();
        $this->assertNull($q->take());
        $this->assertTrue($q->isEmpty());
    }
}
