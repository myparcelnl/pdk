<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Concern;

use MyParcelNL\Pdk\Console\PhpLoader;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ParsesSource
{
    protected function parseSourceDirectories(InputInterface $input, OutputInterface $output): ClassDefinitionCollection
    {
        /** @var \MyParcelNL\Pdk\Console\PhpLoader $loader */
        $loader = Pdk::get(PhpLoader::class);
        $loader->setCommandContext($input, $output);

        /** @var \MyParcelNL\Pdk\Console\Types\Shared\Service\PhpSourceParser $parser */
        $parser = Pdk::get(PhpSourceParser::class);
        $parser->setCommandContext($input, $output);

        $files = $input->getArgument('files');

        $classes = $loader->load($files);

        return $parser->parse($classes);
    }

    /**
     * @param  null|array $default
     */
    protected function registerFilesArgument(array $default = ['src']): void
    {
        $this->addArgument(
            'files',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Files or directories to scan for classes',
            $default
        );
    }
}
