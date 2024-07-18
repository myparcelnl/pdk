<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use DI\Container;
use DI\ContainerBuilder;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Console\Concern\HasCommandContext;
use MyParcelNL\Pdk\Console\Contract\HasCommandContextInterface;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function DI\autowire;
use function DI\value;

abstract class AbstractCommand extends Command implements HasCommandContextInterface
{
    use HasCommandContext;

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    protected $fileSystem;

    /**
     * @throws \Exception
     */
    public function __construct(string $name = null)
    {
        $this->setupPdk();
        $this->fileSystem = PdkFacade::get(FileSystemInterface::class);

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('readCache', InputArgument::OPTIONAL, 'Read from cache', true);
        $this->addArgument('writeCache', InputArgument::OPTIONAL, 'Write to cache', true);

        $this->addOption(
            'rootDir',
            'r',
            InputOption::VALUE_OPTIONAL,
            'The root directory.',
            $this->fileSystem->realpath(__DIR__ . '/../../')
        );
    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setCommandContext($this->getName(), $input, $output);
    }

    /**
     * @return \DI\Container
     * @throws \Exception
     */
    private function buildContainer(): Container
    {
        $builder = new ContainerBuilder();

        $builder->useAutowiring(true);

        $builder->addDefinitions([
            'rootDir' => value(__DIR__ . '/../../'),

            FileSystemInterface::class => autowire(FileSystem::class),
            PdkInterface::class        => autowire(Pdk::class),
        ]);

        return $builder->build();
    }

    /**
     * Set up a bare version of the pdk without the default included configs, so we can have dependency injection.
     *
     * @return void
     * @throws \Exception
     */
    private function setupPdk(): void
    {
        $container = $this->buildContainer();

        $pdk = new Pdk($container);

        Facade::setPdkInstance($pdk);
    }
}
