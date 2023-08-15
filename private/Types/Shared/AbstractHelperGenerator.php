<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Console\Concern\HasCommandContext;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Console\Types\Shared\Concern\ReportsTiming;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Console\Types\Shared\Service\ParsesPhpDocs;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractHelperGenerator
{
    use HasCommandContext;
    use ParsesPhpDocs;
    use ReportsTiming;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection
     */
    protected $definitions;

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    private $fileSystem;

    /**
     * @var array<string,resource>
     */
    private $handles;

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface                           $input
     * @param  \Symfony\Component\Console\Output\OutputInterface                         $output
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection $definitions
     * @param  string                                                                    $baseDir
     */
    public function __construct(
        InputInterface            $input,
        OutputInterface           $output,
        ClassDefinitionCollection $definitions,
        string                    $baseDir
    ) {
        $this->setCommandContext($input, $output);

        $this->definitions = $definitions
            ->filter(function (ClassDefinition $definition): bool {
                return $this->classAllowed($definition);
            });

        $this->baseDir    = $baseDir;
        $this->fileSystem = Pdk::get(FileSystemInterface::class);
    }

    /**
     * @return void
     */
    abstract protected function generate(): void;

    /**
     * @return resource
     */
    public function getHandle(string $filename)
    {
        if (! $this->handles[$filename]) {
            $directory = $this->fileSystem->dirname($filename);

            $this->fileSystem->mkdir($directory);

            $this->handles[$filename] = $this->fileSystem->openStream($filename, 'wb+');
        }

        return $this->handles[$filename];
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $time          = $this->getTime();
        $classBasename = Utils::classBasename(static::class);

        $this->output->writeln(sprintf('🧬 Running %s...', $classBasename));

        $this->generate();

        $this->output->writeln(sprintf('🏁 Finished running %s in %s', $classBasename, $this->printTimeSince($time)));
    }

    /**
     * @param  \MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition $definition
     *
     * @return bool
     */
    protected function classAllowed(ClassDefinition $definition): bool
    {
        $whitelist = $this->getAllowedClasses();

        if (0 === count($whitelist)) {
            return true;
        }

        return (bool) Arr::first($whitelist, static function (string $class) use ($definition): bool {
            return $definition->isSubclassOf($class);
        });
    }

    /**
     * @param  resource $handle
     * @param  string   $filename
     *
     * @return void
     */
    protected function close($handle, string $filename): void
    {
        $this->fileSystem->closeStream($handle);
        $path = $this->fileSystem->realpath($filename);
        $this->output->writeln("️✏️ Wrote to $path");
    }

    /**
     * @return string[]
     */
    protected function getAllowedClasses(): array
    {
        return [];
    }

    /**
     * @param  resource $handle
     * @param  string   $content
     *
     * @return void
     */
    protected function write($handle, string $content): void
    {
        $this->fileSystem->writeToStream($handle, $content);
    }

    /**
     * @param  resource $handle
     * @param  array    $lines
     *
     * @return void
     */
    protected function writeLines($handle, array $lines): void
    {
        $this->write($handle, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}
