<?php

declare(strict_types=1);

namespace ConsoleForge\Console\Commands;

use ConsoleForge\IO;
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
            $io->render(<<<'HTML'
                <div class="px-2 py-1 bg-yellow-600 text-black font-bold">
                    ⚠️  The config/console-forge directory already exists.
                </div>
                <p class="mt-1">Use <span class="font-bold text-yellow">--force</span> to overwrite it.</p>
            HTML);

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
                new \ConsoleForge\Descriptors\CommandDescriptor(
                    name: 'greet',
                    description: 'Say Hello',
                    args: [new \ConsoleForge\Descriptors\ArgDescriptor('name', 'Person name')],
                    opts: [new \ConsoleForge\Descriptors\OptDescriptor('yell', 'y', 'Uppercase')],
                    handler: function (string $name, \ConsoleForge\IO $io, bool $yell = false): int {
                        // using termwind
                        //    $io->render(
                        //        $yell ? "<div class='font-bold uppercase'>Hello, $name!</div>"
                        //              : "<div>Hello, $name!</div>"
                        //    );
                        // return Symfony\Component\Console\Command\Command::SUCCESS;
                
                        // Fallback a SymfonyStyle vía IO
                        $msg = $yell ? strtoupper("Hello, $name!") : "Hello, $name!";
                        $io->writeln($msg);
                        return Symfony\Component\Console\Command\Command::SUCCESS;
                    }
                ),
            ],
        ];
        PHP;

        file_put_contents($exampleFile, $template);

        $io->render(<<<'HTML'
            <div class="px-2 py-1 bg-green-600 text-white font-bold">
                ✅  Configuration directory created successfully: config/console-forge
            </div>
            <p class="mt-1">A sample file was created: <span class="font-bold">example.php</span></p>
        HTML);

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
