<?php

declare(strict_types=1);

namespace ConsoleForge\Console\Commands;

use ConsoleForge\IO;
use ConsoleForge\Support\Notice\Notice;
use ConsoleForge\Support\Notice\TermwindNoticeRenderer;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;

final class InitDirCommand
{
    public function __invoke(IO $io, bool $force = false): int
    {
        $configDir = getcwd().'/config/console-forge';

        if (is_dir($configDir) && ! $force) {
            (new TermwindNoticeRenderer)->render(
                notice: Notice::warning(
                    message: 'The file config/console-forge.php already exists.',
                    detail: 'Use --force to overwrite it.',
                ),
                io: $io,
            );

            return Command::FAILURE;
        }

        if (is_dir($configDir) && $force) {
            $this->deleteDir($configDir);
        }

        mkdir($configDir, 0777, true);

        $exampleFile = $configDir.'/example.php';
        $template = <<<'PHP'
        <?php

        return [
            'commands' => [
                new ConsoleForge\Descriptors\CommandDescriptor(
                    name: 'greetDir',
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

        file_put_contents($exampleFile, $template);

        (new TermwindNoticeRenderer)->render(
            notice: Notice::success(
                message: 'Configuration directory created successfully: config/console-forge',
                detail: 'A sample file with a sample command was created: example.php',
            ),
            io: $io,
        );

        return Command::SUCCESS;
    }

    private function deleteDir(string $dir): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
