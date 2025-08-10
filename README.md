# ConsoleForge

**Framework-agnostic** command definition layer mapped to [symfony/console](https://github.com/symfony/console), with first-class [Termwind](https://github.com/nunomaduro/termwind) integration for a beautiful developer experience.

<p align="center">
    <a href="https://packagist.org/packages/arielespinoza07/console-forge"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/arielespinoza07/console-forge"></a>
    <a href="https://packagist.org/packages/arielespinoza07/console-forge"><img alt="Latest Version" src="https://img.shields.io/packagist/v/arielespinoza07/console-forge"></a>
    <a href="https://packagist.org/packages/arielespinoza07/console-forge"><img alt="License" src="https://img.shields.io/packagist/l/arielespinoza07/console-forge"></a>
</p>

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
use ConsoleForge\Support\Notice\Notice;
use ConsoleForge\Support\Notice\TermwindNoticeRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

$greetCommand = new CommandDescriptor(
    name: 'greet',
    description: 'Say hello to someone',
    args: [
        new ArgDescriptor('name', 'Person name')
    ],
    opts: [
        new OptDescriptor('yell', 'y', 'Uppercase greeting')
    ],
    handler: function (InputInterface $input, IO $io, bool $yell = false): void {
        $name = $input->getArgument('name');
        if ($name === null) {
            (new TermwindNoticeRenderer)->render(
                notice: Notice\Notice::error(
                    message: 'Argument name is required.',
                    detail: 'Try --help for more information.',
                ),
                io: $io,
            );

            return Command::FAILURE;
        }
        $msg = $yell ? strtoupper("Hello, $name!") : "Hello, $name!";
        (new TermwindNoticeRenderer)->render(
            notice: Notice::success(
                message: $msg,
            ),
            io: $io,
        );
        
        return Command::SUCCESS; // exit code
    }
);

```

Invokable class example:

```php
use ConsoleForge\IO;
use ConsoleForge\Support\Notice\Notice;
use ConsoleForge\Support\Notice\SymfonyStyleNoticeRenderer;
use Symfony\Component\Console\Command\Command;

final class CreateUser
{
    public function __invoke(InputInterface $input, IO $io, bool $admin = false): int
    {
        $name = $input->getArgument('name');
        if ($name === null) {
            (new SymfonyStyleNoticeRenderer)->render(
                notice: Notice\Notice::error(
                    message: 'Argument name is required.',
                    detail: 'Try --help for more information.',
                ),
                io: $io,
            );

            return Command::FAILURE;
        }
        (new SymfonyStyleNoticeRenderer())->render(
            notice: Notice::success(
                message: "User {$email} created" . ($admin ? ' as admin' : ''),
            ),
            io: $io,
        );
        
        return Command::SUCCESS; // exit code
    }
}

```

---

2. Register commands in the registry

```php
use ConsoleForge\Descriptors\ArgDescriptor;
use ConsoleForge\Descriptors\CommandDescriptor;
use ConsoleForge\Descriptors\OptDescriptor;
use ConsoleForge\Registry\InMemoryCommandRegistry;

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
use ConsoleForge\App;
use ConsoleForge\Support\ConfigLoader;

// Optional pretty errors (requires nunomaduro/collision)
if (class_exists(\NunoMaduro\Collision\Provider::class)) {
    (new \NunoMaduro\Collision\Provider())->register();
}

if (class_exists(ConfigLoader::class)) {
    // Try loading single-file or directory-based configs.
    ConfigLoader::loadProjectConfigs($registry, getcwd());
}

// Dynamic version if available
$version = class_exists(\Composer\InstalledVersions::class)
        ? (\Composer\InstalledVersions::getPrettyVersion('arielespinoza07/console-forge') ?? 'dev')
        : 'dev';

$app = App::make(
        registry: $registry,
        name: 'ConsoleForge',
        version: $version,
);

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