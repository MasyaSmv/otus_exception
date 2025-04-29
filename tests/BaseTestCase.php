<?php
namespace Tests;

use App\Commands\FailedAfterTwoAttemptsCommand;
use App\Strategies\FailedAfterTwoAttemptsStrategy;
use App\Strategies\RepeatTwiceThenLogStrategy;
use PHPUnit\Framework\TestCase;
use App\Core\ExceptionStrategyRegistry;

abstract class BaseTestCase extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $r = ExceptionStrategyRegistry::instance();
        $r->reset();                         // очищаем карту
        $r->register('*', '*', new RepeatTwiceThenLogStrategy());
        $r->register(
            FailedAfterTwoAttemptsCommand::class,
            '*',
            new FailedAfterTwoAttemptsStrategy()
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        ExceptionStrategyRegistry::instance()->reset();
        parent::tearDown();
    }
}