<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Exception\PdkEndpointException;
use MyParcelNL\Pdk\App\Api\Contract\PdkApiInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PdkEndpoint implements PdkApiInterface
{
    public const CONTEXT_BACKEND  = 'backend';
    public const CONTEXT_FRONTEND = 'frontend';
    public const CONTEXT_SHARED   = 'shared';
    public const CONTEXTS         = [
        self::CONTEXT_BACKEND,
        self::CONTEXT_FRONTEND,
        self::CONTEXT_SHARED,
    ];

    /**
     * @var \MyParcelNL\Pdk\App\Api\PdkActions
     */
    private $actions;

    /**
     * @param  \MyParcelNL\Pdk\App\Api\PdkActions $actions
     */
    public function __construct(PdkActions $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @param  string|\Symfony\Component\HttpFoundation\Request $input
     * @param  string                                           $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call($input, string $context): Response
    {
        try {
            return $this->actions
                ->setContext($context)
                ->execute($input);
        } catch (ApiException $e) {
            return $this->createApiErrorResponse($e);
        } catch (PdkEndpointException $e) {
            return $this->createErrorResponse($e, $e->getStatusCode());
        } catch (Throwable $e) {
            return $this->createErrorResponse($e);
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\Api\Exception\ApiException $exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createApiErrorResponse(ApiException $exception): JsonResponse
    {
        return new JsonResponse(
            [
                'message'    => $exception->getMessage(),
                'request_id' => $exception->getRequestId(),
                'errors'     => $exception->getErrors(),
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param  \Throwable $throwable
     * @param  int        $statusCode
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function createErrorResponse(
        Throwable $throwable,
        int       $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        return new JsonResponse(
            [
                'message' => $throwable->getMessage(),
                'errors'  => [
                    [
                        'status'  => $statusCode,
                        'code'    => $throwable->getCode(),
                        'message' => $throwable->getMessage(),
                        'trace'   => Pdk::isDevelopment()
                            ? $throwable->getTrace()
                            : 'Enable development mode to see stack trace.',
                    ],
                ],
            ],
            $statusCode
        );
    }
}
