<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Concern\ParsesSource;
use MyParcelNL\Pdk\Console\GenerateFactory\Service\CollectionFactoryGeneratorService;
use MyParcelNL\Pdk\Console\GenerateFactory\Service\ModelFactoryGeneratorService;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateFactoryCommand extends AbstractCommand
{
    use ParsesSource;

    protected static $defaultName = 'generate:factory';

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Generate factories for all models');
        $this->addOption('force', 'f', null, 'Overwrite existing files');
        $this->registerFilesArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->parseSourceDirectories($input, $output)
            ->filter(
                fn(ClassDefinition $definition) => $definition->isSubclassOf(Model::class)
                    || $definition->isSubclassOf(Collection::class)
            )
            ->each(function (ClassDefinition $definition) {
                $class = $definition->isSubclassOf(Model::class)
                    ? ModelFactoryGeneratorService::class
                    : CollectionFactoryGeneratorService::class;

                /** @var \MyParcelNL\Pdk\Console\GenerateFactory\Contract\FactoryServiceInterface $service */
                $service = Pdk::get($class);
                $service->setCommandContext($this->input, $this->output);
                $service->generate($definition);
            });

        return self::SUCCESS;
    }
}
