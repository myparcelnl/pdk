<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Concern;

use MyParcelNL\Pdk\Base\Support\Utils;
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

    public function setCommandContext(InputInterface $input, OutputInterface $output): void
    {
        $this->input  = $input;
        $this->output = $output;

        $this->extendOutputStyles($output);
    }

    protected function log(string $content): void
    {
        $this->output->writeln(sprintf('<context>[%s]</context> %s', Utils::classBasename(static::class), $content));
    }

    private function extendOutputStyles(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        $formatter->setStyle('def', new OutputFormatterStyle('cyan', null, ['bold']));
        $formatter->setStyle('success', new OutputFormatterStyle('green'));
        $formatter->setStyle('context', new OutputFormatterStyle('blue', null, ['bold']));
    }
}
