<?php

declare(strict_types=1);

namespace ConsoleForge\Console\Commands;

trait HasTemplate
{
    public function getCommandTemplate(string $name): string
    {
        $template = <<<'PHP'
        <?php
        
        declare(strict_types=1);

        return [
            'commands' => [
                new ConsoleForge\Descriptors\CommandDescriptor(
                    name: '{name}',
                    description: 'Say Hello',
                    args: [new ConsoleForge\Descriptors\ArgDescriptor('name', 'Person name')],
                    opts: [new ConsoleForge\Descriptors\OptDescriptor('yell', 'y', 'Uppercase')],
                    handler: function (Symfony\Component\Console\Input\InputInterface $input, ConsoleForge\IO $io, bool $yell = false): int {
                        $name = $input->getArgument('name');
                        if ($name === null) {
                            (new ConsoleForge\Support\Notice\SymfonyStyleNoticeRenderer)->render(
                                notice: ConsoleForge\Support\Notice\Notice::error(
                                    message: 'Argument name is required, can no be empty.',
                                    detail: 'Try --help for more information.',
                                ),
                                io: $io,
                            );
        
                            return Symfony\Component\Console\Command\Command::FAILURE;
                        }
                        $msg = $yell ? strtoupper("Hello, $name!") : "Hello, $name!";
                        // using termwind
                        //(new ConsoleForge\Support\Notice\TermwindNoticeRenderer)->render(
                        //    notice: ConsoleForge\Support\Notice\Notice::success(
                        //        message: $msg,
                        //    ),
                        //    io: $io,
                        //);
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

        return strtr($template, [
            '{name}' => $name,
        ]);
    }
}
