<?php

declare(strict_types=1);

namespace ConsoleForge\Bridge;

use ConsoleForge\Contracts\ArgDescriptorInterface;
use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Contracts\OptDescriptorInterface;
use ConsoleForge\Handler\HandlerInvoker;
use LogicException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SfCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\renderUsing;

final class SymfonyCommandMapper
{
    private HandlerInvoker $invoker;

    public function __construct(private ?ContainerInterface $container = null)
    {
        $this->invoker = new HandlerInvoker($this->container);
    }

    /**
     * @param  iterable<CommandDescriptorInterface>  $descriptors
     */
    public function attach(Application $app, iterable $descriptors): void
    {
        foreach ($descriptors as $d) {
            $app->add($this->toSymfony($d));
        }
    }

    private function toSymfony(CommandDescriptorInterface $d): SfCommand
    {
        $invoker = $this->invoker;

        return new class($d, $invoker) extends SfCommand
        {
            public function __construct(
                private readonly CommandDescriptorInterface $desc,
                private readonly HandlerInvoker $invoker
            ) {
                parent::__construct($desc->name());

                $this->setDescription($desc->description());
                if ($desc->help()) {
                    $this->setHelp($desc->help());
                }
                if ($desc->hidden()) {
                    $this->setHidden();
                }
            }

            protected function configure(): void
            {
                $this->addArguments();
                $this->addOptions();
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                // Termwind output binding
                renderUsing($output);

                return ($this->invoker)($this->desc->handler(), $input, $output);
            }

            private function addArguments(): void
            {
                /** @var ArgDescriptorInterface $a */
                foreach ($this->desc->args() as $a) {
                    $mode = $a->required() ? InputArgument::REQUIRED : InputArgument::OPTIONAL;
                    if ($a->isArray()) {
                        $mode |= InputArgument::IS_ARRAY;
                    }

                    // Symfony does not allow default in REQUIRED
                    $default = $a->required() ? null : $a->default();

                    $this->addArgument(
                        $a->name(),
                        $mode,
                        $a->description(),
                        $default
                    );
                }
            }

            private function addOptions(): void
            {
                /** @var OptDescriptorInterface $o */
                foreach ($this->desc->opts() as $o) {
                    // Negable => does not accept a value nor is it an array
                    if ($o->negatable() && ($o->acceptValue() || $o->isArray())) {
                        throw new LogicException(sprintf(
                            "Option '--%s': negatable options cannot accept values or be arrays.",
                            $o->name()
                        ));
                    }

                    $mode = $o->acceptValue() ? InputOption::VALUE_REQUIRED : InputOption::VALUE_NONE;
                    if ($o->isArray()) {
                        $mode |= InputOption::VALUE_IS_ARRAY;
                    }
                    if ($o->negatable()) {
                        $mode |= InputOption::VALUE_NEGATABLE;
                    }

                    // Support multiple shortcuts "a|b" -> ['a','b']
                    // Normalize: null or '' => null; otherwise keep string or split into array.
                    $shortcut = $o->shortcut();

                    if ($shortcut === null || $shortcut === '') {
                        $shortcut = null;
                    } elseif (str_contains($shortcut, '|')) {
                        /** @var list<string> $parts */
                        $parts = array_values(array_filter(explode('|', $shortcut), static fn (string $s) => $s !== ''));
                        $shortcut = $parts ?: null; // empty array is equivalent to null for Symfony
                    }
                    // $shortcut is now string|array|null (as Symfony expects)

                    // VALUE_NONE should not be set to default.
                    $default = ($mode & InputOption::VALUE_NONE) === InputOption::VALUE_NONE ? null : $o->default();

                    $this->addOption(
                        $o->name(),
                        $shortcut,
                        $mode,
                        $o->description(),
                        $default
                    );
                }
            }
        };
    }
}
