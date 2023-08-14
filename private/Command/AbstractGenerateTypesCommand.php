<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractGenerateTypesCommand extends Command
{
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
        $output->writeln(sprintf('<info>Running %s...</info>', $this->getName()));

        $generator = $this->getGeneratorClass();

        try {
            $definitions = $this->parseDirectories();

            /** @var \MyParcelNL\Pdk\Console\Types\Shared\AbstractHelperGenerator $generatorInstance */
            $generatorInstance = new $generator($definitions, $this->getBaseDir());

            $generatorInstance->run();
        } catch (Throwable $e) {
            echo $e->getMessage();

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return string
     */
    protected function getBaseDir(): string
    {
        return __DIR__ . '/../..';
    }

    /**
     * @return string[]
     */
    protected function getDirectories(): array
    {
        return [
            'src',
        ];
    }

    /**
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     * @throws \ReflectionException
     */
    private function parseDirectories(): ClassDefinitionCollection
    {
        $parser = new PhpSourceParser();

        foreach ($this->getDirectories() as $directory) {
            $parser->parseDirectory(sprintf('%s/%s', $this->getBaseDir(), $directory));
        }

        return $parser->getDefinitions();
    }
}
