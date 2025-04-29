<?php

use App\Core\ExceptionStrategyRegistry;
use App\Strategies\RepeatOnceThenLogStrategy;

require __DIR__ . '/../vendor/autoload.php';

// Регистрируем дефолтную стратегию:
ExceptionStrategyRegistry::instance()->register(
    commandClass: '*',
    exceptionClass: '*',
    strategy: new RepeatOnceThenLogStrategy()
);
