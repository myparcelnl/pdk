<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Concern;

use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ParsesSource
{
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
    protected function getSourceDirectories(): array
    {
        return [
            'src',
        ];
    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     * @throws \ReflectionException
     */
    protected function parseSourceDirectories(InputInterface $input, OutputInterface $output): ClassDefinitionCollection
    {
        $parser = new PhpSourceParser($input, $output);

        foreach ($this->getSourceDirectories() as $directory) {
            $parser->parseDirectory(sprintf('%s/%s', $this->getBaseDir(), $directory));
        }

        return $parser->getDefinitions();
    }
}
