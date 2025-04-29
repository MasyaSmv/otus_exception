<?php

use App\Core\ExceptionStrategyRegistry;
use App\Strategies\RepeatTwiceThenLogStrategy;

require __DIR__ . '/../vendor/autoload.php';

// Регистрируем дефолтную стратегию:
ExceptionStrategyRegistry::instance()->register('*', '*', new RepeatTwiceThenLogStrategy());
