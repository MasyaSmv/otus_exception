# Otus Exception – учебный микрофреймворк «Очередь → Команды → Стратегии»

[![CI Tests](https://github.com/MasyaSmv/otus_exception/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/MasyaSmv/otus_exception/actions/workflows/php.yml)

> **Кейс ДЗ OTUS:** реализовать набор стратегий обработки исключений так, чтобы  
> код, который вызывает команды, **не менялся** при появлении новых правил.

---

## TL;DR

```bash
git clone https://github.com/MasyaSmv/otus_exception.git
cd otus_exception
composer install          # PHP 8.3+
composer test             # 8 тестов (зелёные)
composer test-coverage    # HTML-отчёт в ./coverage (Lines 100 %)
```

---

## Цель задания

1. Обернуть вызов Команды в блок `try-catch`.
2. Обработчик `catch` должен перехватывать только самое базовое исключение.
3. Есть множество различных обработчиков исключений.
   Выбор подходящего обработчика исключения делается на основе экземпляра перехваченного исключения и команды, которая
   выбросила исключение.
4. Реализовать Команду, которая записывает информацию о выброшенном исключении в лог.
5. Реализовать обработчик исключения, который ставит Команду, пишущую в лог в очередь Команд.
6. Реализовать Команду, которая повторяет Команду, выбросившую исключение.
7. Реализовать обработчик исключения, который ставит в очередь Команду - повторитель команды, выбросившей исключение.
8. С помощью Команд из пункта `4` и пункта `6` реализовать следующую обработку исключений: при первом выбросе исключения
   повторить команду, при повторном выбросе исключения записать информацию в лог.
9. Реализовать стратегию обработки исключения - повторить два раза, потом записать в лог.
   Указание: создать новую команду, точно такую же как в пункте `6`.
   Тип этой команды будет показывать, что Команду не удалось выполнить два раза.

---

## Содержание репозитория

```
src/
 ├─ Core/                 # ядро – очередь, процессор, реестр стратегий
 ├─ Commands/             # команды-«кирпичики» (Log, Repeat*, Failed*)
 └─ Strategies/           # алгоритмы обработки ошибок

tests/
 ├─ Strategies/           # unit-тесты пунктов 4-9 ТЗ
 ├─ Core/                 # покрывают крайние ветки процессора/очереди
 └─ _support/BaseTestCase.php
.github/workflows/php.yml # CI: composer install → phpunit
```

| Папка / файл                      | Что находится                                                                                                                                           |
|-----------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| **src/Core/CommandQueue.php**     | Обёртка над `SplQueue`, FIFO-очередь `CommandInterface`                                                                                                 |
| **src/Core/CommandProcessor.php** | Worker: `take()` → `execute()` → ловит `Throwable`                                                                                                      |
| **src/Core/ExceptionStrategy…**   | `ExceptionStrategyInterface`, singleton-реестр + `reset()`                                                                                              |
| **src/Commands/**                 | <br>• `LogCommand` — пишет строку в `logs/exceptions.log`  <br>• `RepeatCommand` / `RepeatTwiceCommand`  <br>• `FailedAfterTwoAttemptsCommand` — маркер |
| **src/Strategies/**               | <br>• `RepeatOnceThenLogStrategy`  <br>• `RepeatTwiceThenLogStrategy`  <br>• `FailedAfterTwoAttemptsStrategy`                                           |
| **tests/**                        | 8 unit-тестов, изолированы через `BaseTestCase::setUp()`                                                                                                |

---

## Как это работает (1 картинка = 100 слов)

```
┌──────────┐  execute() throws   ┌────────────────────────┐  handle() -> put(...)
│ Command  ├────────────────────►│ CommandProcessor.runOnce│──────────────────────┐
└──────────┘                     └────────────────────────┘                      │
             resolve()                               ┌────────────────┐         ▼
             ┌───────────────┐                       │ExceptionQueue  │◄────────┘
             │ExceptionReg.  │                       └────────────────┘
             └───────────────┘
```

* Команды (любой класс с `execute()`) кладутся в очередь.
* `CommandProcessor` выполняет их по одной.
* При ошибке ищется стратегия по паре «тип команды + тип исключения».
* Стратегия сама решает: повторить, залогировать, отправить маркер и т.д.
* Базовые сценарии, нужные по ДЗ, реализованы стратегиями выше.

---

## Быстрый пример

```php
$queue = new \App\Core\CommandQueue();

$queue->add(new class implements \App\Core\CommandInterface {
    public function execute(): void
    {
        echo "try...\n";
        throw new RuntimeException("boom");
    }
});

$processor = new \App\Core\CommandProcessor($queue);

while (!$queue->isEmpty()) {
    $processor->runOnce();   // 1-й раз → RepeatTwiceCommand
                             // 2-й раз → RepeatTwiceCommand
                             // 3-й раз → FailedAfterTwoAttemptsCommand
                             // 4-й раз → LogCommand, очередь пуста
}
```

---

## Архитектура тестов

* **BaseTestCase** при `setUp()` делает «чистый» реестр и регистрирует все
  рабочие стратегии.
* Тест **CommandProcessorNoStrategyTest** намеренно наследуется прямо от
  `PHPUnit\Framework\TestCase`, чтобы проверить «если стратегий нет — летит
  исключение».
* Каждый тест изолирован (`reset()`).

---

## CI / CD

* GitHub Actions: php-8.3, `composer install`, `phpunit`, coverage (Xdebug).
* Бейдж статуса в заголовке `README`.
* При необходимости легко интегрируется с Codecov / Sonar.

---

## Что можно расширить

| Идея                | Как добавить                                                                          |
|---------------------|---------------------------------------------------------------------------------------|
| Exponential backoff | написать `BackoffCalculator` и стратегию, которая ждёт `sleep()` перед `queue->add()` |
| Slack / Telegram    | создать `NotifySlackCommand`, стратегия кладёт её после маркера                       |
| Конфиг‐в-JSON       | вместо `BaseTestCase::setUp()` считывать `strategies.json`                            |
| Laravel bridge      | сделать `Job`-адаптер: `handle()` → `queue->add()`                                    |

---

## Лицензия

MIT — делайте с кодом всё, что захотите (главное, оставляйте копирайт автора).
