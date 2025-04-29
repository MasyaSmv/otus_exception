<?php

namespace App\Commands;

use App\Core\CommandInterface;
use Throwable;

/**
 * Маркер, показывающий, что исходная команда упала 2 раза.
 * В execute() можно вызывать уведомления или логирование.
 */
readonly class FailedAfterTwoAttemptsCommand implements CommandInterface
{
    public function __construct(
        private CommandInterface $origin,
        private Throwable $exception
    ) {}

    /**
     * @return void
     * @throws Throwable
     */
    public function execute(): void
    {
        throw $this->exception; // чтобы CommandProcessor вызвал стратегию
    }

    /**
     * @return CommandInterface
     */
    public function origin(): CommandInterface
    {
        return $this->origin;
    }

    /**
     * @return Throwable
     */
    public function exception(): Throwable
    {
        return $this->exception;
    }
}
