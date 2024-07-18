<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Concern\ParsesSource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractGenerateTypesCommand extends AbstractCommand
{
    use ParsesSource;

    /**
     * @return class-string<\MyParcelNL\Pdk\Console\Types\Shared\AbstractHelperGenerator>
     */
    abstract protected function getGeneratorClass(): string;

    protected function configure(): void
    {
        parent::configure();
        $this->registerFilesArgument($this->getDefaultSourceDirectories());
    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->log(sprintf('<info>Running %s...</info>', $this->getName()));

        $generator   = $this->getGeneratorClass();
        $definitions = $this->parseSourceDirectories($input, $output);

        $generatorInstance = new $generator($definitions, $input->getOption('rootDir'));
        $generatorInstance->setCommandContext($this->getName(), $input, $output);

        $generatorInstance->run();

        return self::SUCCESS;
    }
}
