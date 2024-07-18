<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Concern\ParsesSource;
use MyParcelNL\Pdk\Console\Types\Shared\Concern\UsesCache;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ParseSourceCommand extends AbstractCommand
{
    use ParsesSource;
    use UsesCache;

    protected static $defaultName = 'parse';

    protected function configure(): void
    {
        parent::configure();

        $this->getDefinition()
            ->getArgument('readCache')
            ->setDefault(false);

        $this->setDescription('Parse source files');
        $this->registerFilesArgument($this->getDefaultSourceDirectories());
    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        if (! $input->getArgument('readCache')) {
            $this->log('Clearing existing cache...');
            $this->clearCache();
        }

        $this->parseSourceDirectories($input, $output);

        return self::SUCCESS;
    }
}
