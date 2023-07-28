<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context\Contract;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * @method static void beforeSuite(BeforeSuiteScope $scope)
 * @method static void beforeFeature(BeforeFeatureScope $scope)
 * @method void beforeScenario(BeforeScenarioScope $scope)
 * @method void beforeStep(BeforeStepScope $scope)
 * @method void afterStep(AfterStepScope $scope)
 * @method void afterScenario(AfterScenarioScope $scope)
 * @method static void afterFeature(AfterFeatureScope $scope)
 * @method static void afterSuite(AfterSuiteScope $scope)
 */
interface ContextInterface extends Context
{
}
