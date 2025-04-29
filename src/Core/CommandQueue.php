<?php

namespace App\Core;

use SplQueue;

class CommandQueue
{
    private SplQueue $queue;

    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    /**
     * @param CommandInterface $cmd
     *
     * @return void
     */
    public function add(CommandInterface $cmd): void
    {
        $this->queue->enqueue($cmd);
    }

    /**
     * @return CommandInterface|null
     */
    public function take(): ?CommandInterface
    {
        if ($this->queue->isEmpty()) {
            return null;
        }
        /** @var CommandInterface $cmd */
        $cmd = $this->queue->dequeue();
        return $cmd;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }
}
