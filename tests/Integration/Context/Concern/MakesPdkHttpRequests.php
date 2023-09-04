<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context\Concern;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Integration\Api\Service\BehatPdkApiService;
use MyParcelNL\Pdk\Tests\Integration\Exception\NoExampleException;

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
        /** @var \MyParcelNL\Pdk\Tests\Integration\Api\Service\BehatPdkApiService $api */
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
     * @param  \MyParcelNL\Pdk\Api\Response\ApiResponse $response
     *
     * @return void
     */
    private function setResponse(ApiResponse $response): void
    {
        $this->response = $response;
    }
}
