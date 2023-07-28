<?php
/** @noinspection PhpUnused,PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use Behat\Gherkin\Node\TableNode;
use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Integration\Context\Concern\MakesHttpRequests;
use Psr\Log\LoggerInterface;

final class RequestContext extends AbstractContext
{
    use MakesHttpRequests;

    /**
     * @Then I expect the response body to contain:
     */
    public function IExpectTheResponseBodyToContain(TableNode $node): void
    {
        $this->IExpectTheResponseToBeSuccessful();

        $body = json_decode($this->response->getBody(), true);

        self::assertIsArray($body, 'Response body is not an array');

        foreach ($this->parseTable($node) as $key => $value) {
            $exists = Arr::has($body, $key);

            self::assertTrue($exists, "Key '$key' does not exist in response body");

            $this->validateValue($key, $value, $body);
        }
    }

    /**
     * @Then I expect the response code to be :responseCode
     */
    public function IExpectTheResponseCodeToBe(int $responseCode): void
    {
        $this->IExpectTheResponseToBeSuccessful();

        self::assertEquals($this->response->getStatusCode(), $responseCode, 'Response code does not match');
    }

    /**
     * @Then I expect the response to be successful
     * @return void
     */
    public function IExpectTheResponseToBeSuccessful(): void
    {
        if (! $this->response instanceof ApiResponse) {
            /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
            $logger = Pdk::get(LoggerInterface::class);

            self::fail(sprintf("Response is null. Logs:\n%s", json_encode($logger->getLogs(), JSON_PRETTY_PRINT)));
        }
    }

    /**
     * @When I do a :method request to action :action
     */
    public function iDoARequestToAction(string $method, string $action): void
    {
        $this->iDoARequestToActionWithParameters($method, $action, '');
    }

    /**
     * @When I do a :method request to action :action with parameters :parameters
     */
    public function iDoARequestToActionWithParameters(string $method, string $action, string $parameters): void
    {
        $this->iDoARequestToActionWithParametersAndBody($method, $action, $parameters);
    }

    /**
     * @When I do a :method request to action :action with parameters :parameters and content:
     */
    public function iDoARequestToActionWithParametersAndBody(
        string     $method,
        string     $action,
        string     $parameters,
        ?TableNode $body = null
    ): void {
        $this->doRequest(
            $method,
            'PDK',
            $this->parseParameters($parameters, ['action' => $action]),
            ['Content-Type' => 'application/json'],
            json_encode($this->parseTable($body))
        );
    }

    /**
     * @Then         show the response in dot notation
     * @return void
     */
    public function showResponseInDotNotation(): void
    {
        $array = Arr::dot($this->getDecodedBody());

        $maxKeyLength   = max(array_map('strlen', array_keys($array)));
        $maxValueLength = max(array_map('strlen', array_map('strval', array_values($array))));

        $log = static function (string $key, $value) use ($maxKeyLength, $maxValueLength) {
            echo sprintf("| %s | %s |\n", str_pad($key, $maxKeyLength), str_pad((string) $value, $maxValueLength));
        };

        $log('key', 'value');

        foreach ($array as $key => $value) {
            $log($key, $value);
        }
    }
}
