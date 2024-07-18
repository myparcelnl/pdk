<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Concern;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see \MyParcelNL\Pdk\Console\Contract\HasCommandContextInterface
 */
trait HasCommandContext
{
    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    private $commandName;

    /**
     * @param  string                                            $name
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function setCommandContext(string $name, InputInterface $input, OutputInterface $output): void
    {
        $this->commandName = $name;
        $this->input       = $input;
        $this->output      = $output;

        $this->extendOutputStyles($output);
    }

    /**
     * @param  string ...$content
     *
     * @return void
     */
    protected function log(string ...$content): void
    {
        $this->output->writeln(sprintf('<context>[%s]</context> %s', $this->commandName, ...$content));
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    private function extendOutputStyles(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        $formatter->setStyle('def', new OutputFormatterStyle('cyan', null, ['bold']));
        $formatter->setStyle('success', new OutputFormatterStyle('green'));
        $formatter->setStyle('context', new OutputFormatterStyle('blue', null, ['bold']));
    }
}
