<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Concern;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function setCommandContext(InputInterface $input, OutputInterface $output): void
    {
        $this->input  = $input;
        $this->output = $output;
    }
}
