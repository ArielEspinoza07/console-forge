# ConsoleForge

**Framework-agnostic** command definition layer mapped to [symfony/console](https://github.com/symfony/console), with first-class [Termwind](https://github.com/nunomaduro/termwind) integration for a beautiful developer experience.

> **Requires [PHP 8.3+](https://php.net/releases/)**
---

## ✨ Features

- **Framework-agnostic**: works in any PHP project (package, CLI tool, framework).
- **Custom command definitions**: define commands as invokable classes or closures — no need to extend `Symfony\Component\Console\Command\Command`.
- **Simple mapping**: a single mapper transforms your definitions into Symfony Console commands.
- **First-class Termwind**: style your CLI output with HTML-like syntax.
- **Optional pretty errors**: integrate [Collision](https://github.com/nunomaduro/collision) for nicer exception output.
- **DI-friendly**: optional PSR-11 container support for handler resolution.

---

## 📦 Installation

```bash
composer require arielespinoza07/console-forge
```

---

## 🚀 Quickstart

1. Create a command handler (closure or class)
   Closure example:

```php
use ConsoleForge\IO;
use ConsoleForge\Descriptors\ArgDescriptor;
use ConsoleForge\Descriptors\CommandDescriptor;
use ConsoleForge\Descriptors\OptDescriptor;
use Symfony\Component\Console\Command\Command;

$greetCommand = new CommandDescriptor(
    name: 'greet',
    description: 'Say hello to someone',
    args: [
        new ArgDescriptor('name', 'Person name')
    ],
    opts: [
        new OptDescriptor('yell', 'y', 'Uppercase greeting')
    ],
    handler: function (string $name, bool $yell = false, IO $io): void {
        $msg = $yell ? strtoupper("Hello, $name!") : "Hello, $name!";
        $io->render("<div class='font-bold text-green'>$msg</div>");
        return Command::SUCCESS; // exit code
    }
);

```

Invokable class example:

```php
use ConsoleForge\IO;
use Symfony\Component\Console\Command\Command;

final class CreateUser
{
    public function __invoke(string $email, bool $admin = false, IO $io): int
    {
        $io->render("<div class='text-green'>User {$email} created" . ($admin ? ' as admin' : '') . "</div>");
        return Command::SUCCESS; // exit code
    }
}

```

---

2. Register commands in the registry

```php
use ConsoleForge\InMemoryCommandRegistry;
use ConsoleForge\Descriptors\ArgDescriptor;
use ConsoleForge\Descriptors\CommandDescriptor;
use ConsoleForge\Descriptors\OptDescriptor;

$registry = new InMemoryCommandRegistry();

$registry->add($greetCommand)
        ->add(new CommandDescriptor(
            name: 'user:create',
            description: 'Create a new user',
            args: [new ArgDescriptor('email', 'User email')],
            opts: [new OptDescriptor('admin', 'a', 'Make admin')],
            handler: CreateUser::class,
        ));

```

---

3. Map to Symfony Console and run

```php
use ConsoleForge\Bridge\SymfonyCommandMapper;
use Symfony\Component\Console\Application;

// Optional pretty errors (requires nunomaduro/collision)
if (class_exists(\NunoMaduro\Collision\Provider::class)) {
    (new \NunoMaduro\Collision\Provider())->register();
}

$app = new Application('ConsoleForge Demo', '0.1.0');

$mapper = new SymfonyCommandMapper();
$mapper->attach($app, $registry->all());

$app->run();
```

---

## 🎨 Styling with Termwind
ConsoleForge uses Termwind out of the box.
You can style your output with HTML-like syntax:

```php
$io->render("<div class='bg-green-500 text-white p-1'>Success!</div>");
```

---

### 🧪 Testing

```bash
composer test
```

---

## 🤝 `CONTRIBUTING.md`

See [CONTRIBUTING](CONTRIBUTING.md) for details.

---

## 📜 License

[MIT License](LICENSE)