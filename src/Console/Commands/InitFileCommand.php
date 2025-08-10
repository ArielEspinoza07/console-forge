<?php

declare(strict_types=1);

namespace ConsoleForge\Console\Commands;

use ConsoleForge\IO;
use ConsoleForge\Support\Notice\Notice;
use ConsoleForge\Support\Notice\TermwindNoticeRenderer;
use Symfony\Component\Console\Command\Command;

final class InitFileCommand
{
    use HasTemplate;

    public function __invoke(IO $io, string $path = '', bool $force = false): int
    {
        $consoleForgeFilePath = $this->getDirectoryPath().'/console-forge.php';

        if (file_exists($consoleForgeFilePath) && ! $force) {
            (new TermwindNoticeRenderer)->render(
                notice: Notice::warning(
                    message: 'The file config/console-forge.php already exists.',
                    detail: 'You may use [--force] option to overwrite it.',
                ),
                io: $io,
            );

            return Command::FAILURE;
        }

        if ($force) {
            $response = $io->ask('Are you sure you want to overwrite the existing file {config/console-forge.php}? (y/n)');
            if ($response !== 'y') {
                (new TermwindNoticeRenderer)->render(
                    notice: Notice::info(
                        message: 'Configuration directory not overwritten.',
                    ),
                    io: $io,
                );

                return Command::FAILURE;
            }
            $this->deleteFile($consoleForgeFilePath, $io);
        }

        $this->createFile($consoleForgeFilePath, $io);

        $template = $this->getCommandTemplate('greet:file');

        $this->storeTemplateOnDirectory($consoleForgeFilePath, $template, $io);

        (new TermwindNoticeRenderer)->render(
            notice: Notice::success(
                message: 'Command executed successfully,',
                detail: 'You can now edit it and add your commands, use sample for guidance',
            ),
            io: $io,
        );

        return Command::SUCCESS;
    }

    private function createFile(string $configPath, IO $io): void
    {
        mkdir(dirname($configPath), 0750, true);
        (new TermwindNoticeRenderer)->render(
            notice: Notice::info(
                message: 'console-forge.php file created.',
            ),
            io: $io,
        );
    }

    private function deleteFile(string $consoleForgeFilePath, IO $io): void
    {
        unlink($consoleForgeFilePath);
        if (is_dir($this->getDirectoryPath())) {
            rmdir($this->getDirectoryPath());
        }
        (new TermwindNoticeRenderer)->render(
            notice: Notice::info(
                message: 'console-forge.php file deleted.',
            ),
            io: $io,
        );
    }

    private function getDirectoryPath(): string
    {
        return getcwd().'/config';
    }

    private function storeTemplateOnDirectory(string $configPath, string $template, IO $io): void
    {
        file_put_contents($configPath, $template);
        (new TermwindNoticeRenderer)->render(
            notice: Notice::info(
                message: 'A sample command was created.',
            ),
            io: $io,
        );
    }
}
