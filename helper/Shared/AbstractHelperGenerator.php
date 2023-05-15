<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Helper\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Helper\Shared\Concern\HasLogging;
use MyParcelNL\Pdk\Helper\Shared\Model\ClassDefinition;
use MyParcelNL\Pdk\Helper\Shared\Service\ParsesPhpDocs;
use RuntimeException;

abstract class AbstractHelperGenerator
{
    use HasLogging;
    use ParsesPhpDocs;

    /**
     * @var \MyParcelNL\Pdk\Helper\Shared\Collection\ClassDefinitionCollection
     */
    protected $definitions;

    /**
     * @var array<string,resource>
     */
    private $handles;

    /**
     * @param  \MyParcelNL\Pdk\Helper\Shared\Collection\ClassDefinitionCollection $definitions
     */
    public function __construct(ClassDefinitionCollection $definitions)
    {
        $this->definitions = $definitions
            ->filter(function (ClassDefinition $definition): bool {
                return $this->classAllowed($definition);
            });
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
            $directory = dirname($filename);

            if (! is_dir($directory)
                && ! mkdir($concurrentDirectory = $directory, 0755, true)
                && ! is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            $this->handles[$filename] = fopen($filename, 'wb+');
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

        $this->log('🧬', sprintf('Running %s...', $classBasename));

        $this->generate();

        $this->log('🏁', sprintf('Finished running %s in %s', $classBasename, $this->printTimeSince($time)));
    }

    /**
     * @param  \MyParcelNL\Pdk\Helper\Shared\Model\ClassDefinition $definition
     *
     * @return bool
     */
    protected function classAllowed(ClassDefinition $definition): bool
    {
        $whitelist = $this->getAllowedClasses();

        if (count($whitelist) === 0) {
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
        fclose($handle);
        $path = realpath($filename);
        $this->log('✏️', "Wrote to $path");
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
        fwrite($handle, $content);
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
