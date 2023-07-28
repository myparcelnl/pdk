<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context\Concern;

use Behat\Gherkin\Node\TableNode;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use Throwable;

trait MakesHttpRequests
{
    /**
     * @var \MyParcelNL\Pdk\Api\Response\ApiResponse
     */
    protected $response;

    /**
     * @param  string $method
     * @param  string $path
     * @param  array  $parameters
     * @param  array  $headers
     * @param  string $body
     *
     * @return void
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function doRequest(
        string $method = 'GET',
        string $path = '/',
        array  $parameters = [],
        array  $headers = [],
        string $body = ''
    ): void {
        expect(function () use ($headers, $body, $parameters, $path, $method) {
            /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
            $api = Pdk::get(ApiServiceInterface::class);

            $request = new Request([
                'body'       => $body,
                'headers'    => $headers,
                'method'     => $method,
                'parameters' => $parameters,
                'path'       => $path,
            ]);

            /** @var \MyParcelNL\Pdk\Api\Response\ApiResponse $response */
            $response = $api->doRequest($request);

            $this->setResponse($response);
        })->not->toThrow(Throwable::class);
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
     * @param  string $parameters
     * @param  array  $initial
     *
     * @return string[]
     */
    protected function parseParameters(string $parameters, array $initial = []): array
    {
        return array_reduce(
            explode('&', $parameters),
            static function (array $carry, string $item): array {
                [$key, $value] = explode('=', $item);

                $carry[$key] = $value;

                return $carry;
            },
            $initial
        );
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
