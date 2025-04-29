<?php

use App\Commands\FailedAfterTwoAttemptsCommand;
use App\Core\ExceptionStrategyRegistry;
use App\Strategies\FailedAfterTwoAttemptsStrategy;
use App\Strategies\RepeatTwiceThenLogStrategy;

require __DIR__ . '/../vendor/autoload.php';

$reg = ExceptionStrategyRegistry::instance();
$reg->register('*', '*', new RepeatTwiceThenLogStrategy()); // базовая
$reg->register(FailedAfterTwoAttemptsCommand::class, '*', new FailedAfterTwoAttemptsStrategy());

