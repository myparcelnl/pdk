<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Contract;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see \MyParcelNL\Pdk\Console\Concern\HasCommandContext
 */
interface HasCommandContextInterface
{
    public function setCommandContext(InputInterface $input, OutputInterface $output): void;
}
