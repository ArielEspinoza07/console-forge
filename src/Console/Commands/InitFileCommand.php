<?php

declare(strict_types=1);

namespace ConsoleForge\Console\Commands;

use ConsoleForge\IO;
use ConsoleForge\Support\Notice\Notice;
use ConsoleForge\Support\Notice\TermwindNoticeRenderer;
use Symfony\Component\Console\Command\Command;

final class InitFileCommand
{
    public function __invoke(IO $io, bool $force = false): int
    {
        $configPath = getcwd().'/config/console-forge.php';

        if (file_exists($configPath) && ! $force) {
            (new TermwindNoticeRenderer)->render(
                notice: Notice::warning(
                    message: 'The file config/console-forge.php already exists.',
                    detail: 'You may use [--force] option to overwrite it.',
                ),
                io: $io,
            );

            return Command::FAILURE;
        }

        if (! is_dir(dirname($configPath))) {
            mkdir(dirname($configPath), 0777, true);
        }

        $template = <<<'PHP'
        <?php

        return [
            'commands' => [
                new ConsoleForge\Descriptors\CommandDescriptor(
                    name: 'greetFile',
                    description: 'Say Hello',
                    args: [new ConsoleForge\Descriptors\ArgDescriptor('name', 'Person name')],
                    opts: [new ConsoleForge\Descriptors\OptDescriptor('yell', 'y', 'Uppercase')],
                    handler: function (string $name, ConsoleForge\IO $io, bool $yell = false): int {
                        $msg = $yell ? strtoupper("Hello, $name!") : "Hello, $name!";
                        // using termwind
                        //(new ConsoleForge\Support\Notice\TermwindNoticeRenderer)->render(
                        //    notice: ConsoleForge\Support\Notice\Notice::success(
                        //        message: $msg,
                        //    ),
                        //    io: $io,
                        //);
                        //    $io->render(
                        //        $yell ? "<div class='font-bold uppercase'>Hello, $name!</div>"
                        //              : "<div>Hello, $name!</div>"
                        //    );
                        // return Symfony\Component\Console\Command\Command::SUCCESS;
                
                        // Fallback a SymfonyStyle vía IO
                        (new ConsoleForge\Support\Notice\SymfonyStyleNoticeRenderer())->render(
                            notice: ConsoleForge\Support\Notice\Notice::success(
                                message: $msg,
                            ),
                            io: $io,
                        );
                        
                        return Symfony\Component\Console\Command\Command::SUCCESS;
                    }
                ),
            ],
        ];
        PHP;

        file_put_contents($configPath, $template);

        (new TermwindNoticeRenderer)->render(
            notice: Notice::success(
                message: 'Configuration file created successfully: config/console-forge.php',
                detail: 'An sample command was created inside, for guidance',
            ),
            io: $io,
        );

        return Command::SUCCESS;
    }
}
