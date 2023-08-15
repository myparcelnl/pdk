<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Concern\ParsesSource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractGenerateTypesCommand extends AbstractCommand
{
    use ParsesSource;

    /**
     * @return class-string<\MyParcelNL\Pdk\Console\Types\Shared\AbstractHelperGenerator>
     */
    abstract protected function getGeneratorClass(): string;

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $output->writeln(sprintf('<info>Running %s...</info>', $this->getName()));

        $generator = $this->getGeneratorClass();

        try {
            $definitions = $this->parseSourceDirectories($input, $output);

            /** @var \MyParcelNL\Pdk\Console\Types\Shared\AbstractHelperGenerator $generatorInstance */
            $generatorInstance = new $generator(
                $input,
                $output,
                $definitions,
                $this->getBaseDir()
            );

            $generatorInstance->run();
        } catch (Throwable $e) {
            echo $e->getMessage();

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
