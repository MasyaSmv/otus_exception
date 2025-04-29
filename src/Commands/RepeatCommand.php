<?php

namespace App\Commands;

use App\Core\CommandInterface;
use Throwable;

readonly class RepeatCommand implements CommandInterface
{
    public function __construct(
        private CommandInterface $origin,
        private int $maxAttempts,
        private int $attempt = 1 // счётчик начнётся со 1-го запуска
    )
    {
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function execute(): void
    {
        $this->origin->execute();
    }

    /**
     * Удобный геттер для стратегий
     *
     * @return int
     */
    public function attemptsLeft(): int
    {
        return $this->maxAttempts - $this->attempt;
    }

    /**
     * Создаём такую же команду, но с увеличенным счётчиком
     *
     * @return self
     */
    public function nextAttempt(): self
    {
        return new self($this->origin, $this->maxAttempts, $this->attempt + 1);
    }
}
