<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Factory\MockPdkFactory;
use MyParcelNL\Pdk\Contract\MockServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\Facade\Mock;
use Nette\Loaders\RobotLoader;
use ReflectionClass;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Contracts\Service\ResetInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use MatchesSnapshots;

    private const MOCKS_DIR = __DIR__ . '/../mocks';

    private static $services = [];

    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        self::instantiatePdk();
        self::getServices();
    }

    /**
     * @return array
     */
    private static function getServices(): array
    {
        if (empty(self::$services)) {
            $loader = new RobotLoader();
            $loader->addDirectory(self::MOCKS_DIR);
            $loader->rebuild();

            $classes = array_keys($loader->getIndexedClasses());

            self::$services = array_filter($classes, static function (string $class) {
                return is_subclass_of($class, MockServiceInterface::class);
            });
        }

        return self::$services;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private static function instantiatePdk(): void
    {
        if (Facade::getPdkInstance()) {
            return;
        }

        MockPdkFactory::create();
    }

    protected function tearDown(): void
    {
        Mock::reset();

        $this->resetServices();
    }

    /**
     * @param  \ReflectionClass $ref
     *
     * @return void
     */
    private function resetCoreServices(ReflectionClass $ref): void
    {
        foreach ($ref->getInterfaces() as $interface) {
            if (in_array($interface->getName(), [MockServiceInterface::class, ResetInterface::class], true)) {
                continue;
            }

            $instance = Pdk::get($interface->getName());

            $instance->reset();
        }
    }

    /**
     * @return void
     */
    private function resetServices(): void
    {
        foreach (self::getServices() as $class) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $ref = new ReflectionClass($class);

            if (! $ref->implementsInterface(MockServiceInterface::class)) {
                continue;
            }

            $this->resetCoreServices($ref);
        }
    }
}
