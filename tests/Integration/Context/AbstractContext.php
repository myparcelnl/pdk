<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Hook\Scope\HookScope;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Bootstrap\TestCase;
use MyParcelNL\Pdk\Tests\Integration\Context\Concern\ResolvesModels;
use MyParcelNL\Pdk\Tests\Integration\Context\Concern\ValidatesValues;
use MyParcelNL\Pdk\Tests\Integration\Context\Contract\ContextInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractContext extends TestCase implements ContextInterface
{
    use ValidatesValues;
    use ResolvesModels;

    private const BEFORE_STEP     = 'beforeStep';
    private const BEFORE_SUITE    = 'beforeSuite';
    private const BEFORE_SCENARIO = 'beforeScenario';
    private const AFTER_SUITE     = 'afterSuite';
    private const AFTER_STEP      = 'afterStep';
    private const AFTER_SCENARIO  = 'afterScenario';
    private const AFTER_FEATURE   = 'afterFeature';
    private const BEFORE_FEATURE  = 'beforeFeature';

    /**
     * @var array
     */
    private static $hooks = [];

    /**
     * @param  null|string $name
     * @param  array       $data
     * @param  string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @AfterFeature
     *
     * @param  \Behat\Behat\Hook\Scope\AfterFeatureScope $scope
     *
     * @return void
     */
    final public static function afterFeature(AfterFeatureScope $scope): void
    {
        self::executeHooks(self::AFTER_FEATURE, $scope);
    }

    /**
     * @AfterSuite
     *
     * @param  \Behat\Testwork\Hook\Scope\AfterSuiteScope $scope
     *
     * @return void
     */
    final public static function afterSuite(AfterSuiteScope $scope): void
    {
        self::executeHooks(self::AFTER_SUITE, $scope);
    }

    /**
     * @BeforeFeature
     *
     * @param  \Behat\Behat\Hook\Scope\BeforeFeatureScope $scope
     *
     * @return void
     */
    final public static function beforeFeature(BeforeFeatureScope $scope): void
    {
        self::executeHooks(self::BEFORE_FEATURE, $scope);
    }

    /**
     * @BeforeSuite
     *
     * @param  \Behat\Testwork\Hook\Scope\BeforeSuiteScope $scope
     *
     * @return void
     */
    final public static function beforeSuite(BeforeSuiteScope $scope): void
    {
        self::executeHooks(self::BEFORE_SUITE, $scope);
    }

    /**
     * @param  string $message
     *
     * @return string
     */
    protected static function withLogs(string $message): string
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
        $logger   = Pdk::get(LoggerInterface::class);
        $logsJson = json_encode($logger->getLogs(), JSON_PRETTY_PRINT);

        return trim(sprintf('%s Logs: %s', $message, $logsJson));
    }

    /**
     * @param  string                               $hook
     * @param  \Behat\Testwork\Hook\Scope\HookScope $scope
     *
     * @return void
     */
    private static function executeHooks(string $hook, HookScope $scope): void
    {
        foreach (self::$hooks[$hook] ?? [] as $callable) {
            $callable($scope);
        }
    }

    /**
     * @param  string $hook
     *
     * @return void
     */
    private static function resetHooks(string $hook): void
    {
        self::$hooks[$hook] = [];
    }

    /**
     * @AfterScenario
     *
     * @param  \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
     *
     * @return void
     */
    final public function afterScenario(AfterScenarioScope $scope): void
    {
        self::executeHooks(self::AFTER_SCENARIO, $scope);
    }

    /**
     * @AfterStep
     *
     * @param  \Behat\Behat\Hook\Scope\AfterStepScope $scope
     *
     * @return void
     */
    final public function afterStep(AfterStepScope $scope): void
    {
        self::executeHooks(self::AFTER_STEP, $scope);
    }

    /**
     * @BeforeScenario
     *
     * @param  \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
     *
     * @return void
     */
    final public function beforeScenario(BeforeScenarioScope $scope): void
    {
        self::executeHooks(self::BEFORE_SCENARIO, $scope);
    }

    /**
     * @BeforeStep
     *
     * @param  \Behat\Behat\Hook\Scope\BeforeStepScope $scope
     *
     * @return void
     */
    final public function beforeStep(BeforeStepScope $scope): void
    {
        self::executeHooks(self::BEFORE_STEP, $scope);
    }

    /**
     * @return string
     */
    protected function getValidApiKey(): string
    {
        return getenv(sprintf('API_KEY_%s', strtoupper(Platform::getPlatform()))) ?: TestBootstrapper::API_KEY_VALID;
    }

    /**
     * @param  string $method
     *
     * @return void
     */
    protected function markDebugMethod(string $method): void
    {
        self::markTestIncomplete(sprintf('Step %s is only for debugging purposes. Remove it when done.', $method));
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $model
     * @param  string                                     $key
     *
     * @return null|mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function matchModelProperty(?Model $model, string $key)
    {
        return $model->getAttribute(strtolower($key));
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onAfterFeature(callable $callable): void
    {
        $this->addHook(self::AFTER_FEATURE, $callable);
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onAfterScenario(callable $callable): void
    {
        $this->addHook(self::AFTER_SCENARIO, $callable);
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onAfterStep(callable $callable): void
    {
        $this->addHook(self::AFTER_STEP, $callable);
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onAfterSuite(callable $callable): void
    {
        $this->addHook(self::AFTER_SUITE, $callable);
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onBeforeFeature(callable $callable): void
    {
        $this->addHook(self::BEFORE_FEATURE, $callable);
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onBeforeScenario(callable $callable): void
    {
        $this->addHook(self::BEFORE_SCENARIO, $callable);
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onBeforeStep(callable $callable): void
    {
        $this->addHook(self::BEFORE_STEP, $callable);
    }

    /**
     * @param  callable $callable
     *
     * @return void
     */
    protected function onBeforeSuite(callable $callable): void
    {
        $this->addHook(self::BEFORE_SUITE, $callable);
    }

    /**
     * @param  null|\Behat\Gherkin\Node\TableNode $table
     *
     * @return array
     */
    protected function parseTable(?TableNode $table): ?array
    {
        if (! $table) {
            return null;
        }

        $collection = new Collection($table->getRows());

        return $collection
            ->mapWithKeys(static function (array $item): array {
                [$key, $value] = $item;

                return [$key => $value];
            })
            ->filter(static function ($value, string $key): bool {
                return 'key' !== $key;
            })
            ->toArray();
    }

    /**
     * @param  string   $hook
     * @param  callable $callable
     *
     * @return void
     */
    private function addHook(string $hook, callable $callable): void
    {
        if (! isset(self::$hooks[$hook])) {
            self::resetHooks($hook);
        }

        self::$hooks[$hook][] = $callable;
    }
}
