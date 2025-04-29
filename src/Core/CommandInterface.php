<?php

namespace App\Core;

use Throwable;

interface CommandInterface
{
    /**
     * Выполняет логику команды.
     *
     * @throws Throwable  Команда может бросить ЛЮБОЕ исключение
     */
    public function execute(): void;
}
