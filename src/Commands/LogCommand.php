<?php

namespace App\Commands;

use App\Core\CommandInterface;
use Throwable;

readonly class LogCommand implements CommandInterface
{
    public function __construct(
        private Throwable $exception,
        private CommandInterface $failedCommand,
        private string $logFile = __DIR__ . '/../../logs/exceptions.log'
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $line = sprintf(
            "[%s] %s | %s: %s\n",
            date('c'),
            $this->failedCommand::class,
            $this->exception::class,
            $this->exception->getMessage(),
        );

        // простейший синхронный лог; в реальном коде — PSR-3 логгер
        file_put_contents($this->logFile, $line, FILE_APPEND);
    }
}
