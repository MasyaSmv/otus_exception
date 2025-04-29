<?php

namespace App\Core;

use Throwable;

readonly class CommandProcessor
{
    public function __construct(private CommandQueue $queue) {}

    /**
     * @throws Throwable
     */
    public function runOnce(): void
    {
        $cmd = $this->queue->take();
        if ($cmd === null) {
            // ничего в очереди
            return;                
        }

        try {
            $cmd->execute();
        } catch (Throwable $e) {
            $strategy = ExceptionStrategyRegistry::instance()->resolve($cmd, $e);

            if ($strategy !== null) {
                $strategy->handle($cmd, $e, $this->queue);
            } else {
                // если стратегии нет – пробрасываем выше (сломает воркер → увидим баг в тестах/логах)
                throw $e;
            }
        }
    }
}
