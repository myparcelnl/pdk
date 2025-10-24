<?php
/** @noinspection PhpUnused,PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use Behat\Gherkin\Node\TableNode;
use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Tests\Integration\Context\Concern\MakesPdkHttpRequests;

/**
 * This context is for tests that do requests to the PDK API.
 */
final class RequestContext extends AbstractContext
{
    use MakesPdkHttpRequests;

    /**
     * @param  null|string $name
     * @param  array       $data
     * @param  string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->onAfterScenario(function () {
            $this->response = null;
        });
    }

    /**
     * @Then I expect error code :code in the response
     */
    public function IExpectErrorCodeInTheResponse(string $code): void
    {
        $this->IExpectTheResponseToBeSuccessful();
        $body = $this->getDecodedBody();
        self::assertArrayHasKey('errors', $body, self::withLogs('Response does not contain errors.'));
        $errors = $body['errors'] ?? [];
        $found  = false;
        foreach ($errors as $error) {
            if ((string) ($error['code'] ?? '') === $code) {
                $found = true;
                break;
            }
        }
        self::assertTrue($found, self::withLogs("Error code '$code' not found in response."));
    }

    /**
     * @Then I expect the response body to contain:
     */
    public function IExpectTheResponseBodyToContain(TableNode $node): void
    {
        $this->IExpectTheResponseToBeSuccessful();

        $body = $this->response->getBody();

        self::assertNotNull($body, 'Response body is null');

        $body = json_decode($body, true);

        foreach ($this->parseTable($node) as $key => $value) {
            $exists = Arr::has($body, $key);

            if ('NULL' === $value) {
                self::assertFalse($exists, "Key '$key' exists in response body");
            } else {
                self::assertTrue($exists, "Key '$key' does not exist in response body");
            }

            $this->validateValue($key, $value, $body);
        }
    }

    /**
     * @Then I expect the response code to be :statusCode
     */
    public function IExpectTheResponseCodeToBe(int $statusCode): void
    {
        $this->IExpectTheResponseToBeSuccessful();

        $actualStatusCode = $this->response->getStatusCode();

        self::assertEquals($actualStatusCode, $statusCode, self::withLogs('Response code does not match.'));
    }

    /**
     * @Then I expect the response to be successful
     * @return void
     */
    public function IExpectTheResponseToBeSuccessful(): void
    {
        if (! $this->response instanceof ApiResponse) {
            self::fail(self::withLogs('Response is null.'));
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
     * @When I do a :method request to action :action with content:
     */
    public function iDoARequestToActionWithContent(string $method, string $action, ?TableNode $body = null): void
    {
        $this->iDoARequestToActionWithParametersAndContent($method, $action, '', $body);
    }

    /**
     * @When I do a :method request to action :action with parameters :parameters
     */
    public function iDoARequestToActionWithParameters(string $method, string $action, string $parameters): void
    {
        $this->iDoARequestToActionWithParametersAndContent($method, $action, $parameters);
    }

    /**
     * @When I do a :method request to action :action with parameters :parameters and content:
     */
    public function iDoARequestToActionWithParametersAndContent(
        string     $method,
        string     $action,
        string     $parameters = '',
        ?TableNode $body = null
    ): void {
        $this->doPdkRequest(
            $method,
            $this->createParameters($parameters, ['action' => $action]),
            ['Content-Type' => 'application/json'],
            $body ? json_encode(Arr::undot($this->parseTable($body))) : null
        );
    }

    /**
     * Debug step used to show the response in dot notation, for easy copy-pasting into a table in the feature file.
     *
     * @Then         show the response in dot notation
     * @return void
     * @noinspection ForgottenDebugOutputInspection
     */
    public function showResponseInDotNotation(): void
    {
        $array = Arr::dot($this->getDecodedBody());

        $maxKeyLength   = max(array_map('strlen', array_keys($array)));
        $maxValueLength = max(array_map('strlen', array_map('strval', array_values($array))));

        $log = static function (string $key, $value) use ($maxKeyLength, $maxValueLength) {
            $message = sprintf(
                '| %s | %s |',
                str_pad($key, $maxKeyLength),
                str_pad((string) $value, $maxValueLength)
            );

            error_log($message);
        };

        $log('key', 'value');

        foreach ($array as $key => $value) {
            $log($key, $value);
        }

        $this->markDebugMethod(__METHOD__);
    }
}
