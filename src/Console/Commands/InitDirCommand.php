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
    use HasTemplate;

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
            $response = $io->ask('Are you sure you want to overwrite the existing directory {config/console-forge/}? (y/n)');
            if ($response !== 'y') {
                (new TermwindNoticeRenderer)->render(
                    notice: Notice::info(
                        message: 'Configuration directory not overwritten.',
                    ),
                    io: $io,
                );

                return Command::FAILURE;
            }
            $this->deleteDir($configDir, $io);
        }

        $this->createDirectory($configDir, $io);
        $this->storeExampleOnDirectory($configDir, $io);

        (new TermwindNoticeRenderer)->render(
            notice: Notice::success(
                message: 'Command executed successfully,',
                detail: 'You can create your own files and put your commands, use the sample as a guide',
            ),
            io: $io,
        );

        return Command::SUCCESS;
    }

    private function createDirectory(string $dir, IO $io): void
    {
        mkdir($dir, 0750, true);
        (new TermwindNoticeRenderer)->render(
            notice: Notice::info(
                message: 'config/console-forge directory created successfully.',
            ),
            io: $io,
        );
    }

    private function deleteDir(string $dir, IO $io): void
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

        (new TermwindNoticeRenderer)->render(
            notice: Notice::info(
                message: 'config/console-forge directory deleted.',
            ),
            io: $io,
        );
    }

    public function storeExampleOnDirectory(string $configDir, IO $io): void
    {
        $exampleFile = $configDir.'/example.php';
        $template = $this->getCommandTemplate('greet:dir');

        file_put_contents($exampleFile, $template);
        (new TermwindNoticeRenderer)->render(
            notice: Notice::info(
                message: 'Sample file created [example.php]',
            ),
            io: $io,
        );
    }
}
