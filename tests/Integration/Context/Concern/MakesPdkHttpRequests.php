<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context\Concern;

use Behat\Gherkin\Node\TableNode;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Exception\NoExampleException;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Api\Service\BehatPdkApiService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;

trait MakesPdkHttpRequests
{
    /**
     * @var \MyParcelNL\Pdk\Api\Response\ApiResponse|null
     */
    protected $response;

    /**
     * @param  string $parameters
     * @param  array  $initial
     *
     * @return string[]
     */
    protected function createParameters(string $parameters, array $initial = []): array
    {
        parse_str($parameters, $parsedParameters);

        return array_merge($initial, $parsedParameters);
    }

    /**
     * @param  string      $method
     * @param  array       $parameters
     * @param  array       $headers
     * @param  null|string $body
     *
     * @return void
     * @noinspection PhpRedundantCatchClauseInspection
     */
    protected function doPdkRequest(
        string  $method,
        array   $parameters,
        array   $headers,
        ?string $body
    ): void {
        /** @var \MyParcelNL\Pdk\Api\Service\BehatPdkApiService $api */
        $api = Pdk::get(BehatPdkApiService::class);

        try {
            $request = new Request(compact('method', 'parameters', 'headers', 'body'));

            /** @var \MyParcelNL\Pdk\Api\Response\ApiResponse $response */
            $response = $api->doRequest($request, ApiResponseWithBody::class);
        } catch (NoExampleException $e) {
            self::markTestIncomplete($e->getMessage());
        } catch (ApiException $e) {
            $response = new ApiResponseWithBody($e->getResponse());
        }

        $this->setResponse($response);
    }

    /**
     * @return array
     */
    protected function getDecodedBody(): array
    {
        $this->IExpectTheResponseToBeSuccessful();
        $body = $this->response->getBody();

        self::assertIsString($body, 'Response body is not a string');

        $array = json_decode($body, true);

        self::assertIsArray($array, 'Parsed response body is not an array');
        self::assertNotEmpty($array, 'Parsed response body is empty');

        return $array;
    }

    /**
     * @param  null|\Behat\Gherkin\Node\TableNode $table
     *
     * @return array[]
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
     * @param  \MyParcelNL\Pdk\Api\Response\ApiResponse $response
     *
     * @return void
     */
    private function setResponse(ApiResponse $response): void
    {
        $this->response = $response;
    }
}
