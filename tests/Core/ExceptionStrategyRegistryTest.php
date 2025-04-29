<?php

namespace Tests\Core;

use App\Core\CommandInterface;
use App\Core\CommandQueue;
use App\Core\ExceptionStrategyInterface;
use App\Core\ExceptionStrategyRegistry;
use RuntimeException;
use Tests\BaseTestCase;
use Throwable;

class ExceptionStrategyRegistryTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testWildcardFallbackIsReturned(): void
    {
        $dummy = new class implements ExceptionStrategyInterface {
            public function handle(CommandInterface $cmd, Throwable $e, CommandQueue $queue): void
            {
            }
        };

        $r = ExceptionStrategyRegistry::instance();
        $r->reset();                                   // обнуляем карту
        $r->register('*', '*', $dummy);

        $this->assertSame(
            $dummy,
            $r->resolve(
                new class implements CommandInterface {
                    public function execute(): void
                    {
                    }
                },
                new RuntimeException(),
            ),
        );
    }
}
