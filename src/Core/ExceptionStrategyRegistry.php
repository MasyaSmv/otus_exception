<?php

namespace App\Core;

use Throwable;

/**
 * Регистрирует «какую стратегию применить» к паре (типКоманды, типИсключения).
 * Пока делаем примитивный Singleton-реестр, позже заменим
 * на DI/конфиг из JSON – не критично для ДЗ.
 */
class ExceptionStrategyRegistry
{
    /** @var array<class-string, array<class-string, ExceptionStrategyInterface>> */
    private array $map = [];

    private static ?self $instance = null;

    private function __construct(){}

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * @param string $commandClass
     * @param string $exceptionClass
     * @param ExceptionStrategyInterface $strategy
     *
     * @return void
     */
    public function register(
        string $commandClass,
        string $exceptionClass,
        ExceptionStrategyInterface $strategy
    ): void {
        $this->map[$commandClass][$exceptionClass] = $strategy;
    }

    /**
     * @param CommandInterface $cmd
     * @param Throwable $e
     *
     * @return ExceptionStrategyInterface|null
     */
    public function resolve(CommandInterface $cmd, Throwable $e): ?ExceptionStrategyInterface
    {
        $c = $cmd::class;
        $t = $e::class;

        return $this->map[$c][$t]
            ?? $this->map[$c]['*']
            ?? $this->map['*'][$t]
            ?? $this->map['*']['*']
            ?? null;
    }
}
