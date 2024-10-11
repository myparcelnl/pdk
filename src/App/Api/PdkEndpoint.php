<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Exception\PdkEndpointException;
use MyParcelNL\Pdk\App\Api\Contract\PdkApiInterface;
use MyParcelNL\Pdk\Facade\Logger;
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
            // In case of an ApiException, AbstractApiService has already logged the error.
            return $this->createApiErrorResponse($e);
        } catch (Throwable $e) {
            if ($e instanceof PdkEndpointException) {
                $response = $this->createErrorResponse($context, $e, $e->getStatusCode());
            } else {
                $response = $this->createErrorResponse($context, $e);
            }

            Logger::error('An exception was thrown while executing an action', [
                'action'   => is_string($input) ? $input : $input->get('action') ?? 'unknown',
                'context'  => $context,
                // Pass backend context to log stack traces.
                'response' => $this->createErrorContext(self::CONTEXT_BACKEND, $e),
            ]);

            return $response;
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\Api\Exception\ApiException $exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createApiErrorResponse(ApiException $exception): JsonResponse
    {
        return new JsonResponse([
            'message'    => $exception->getMessage(),
            'request_id' => $exception->getRequestId(),
            'errors'     => $exception->getErrors(),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param  string     $context
     * @param  \Throwable $throwable
     * @param  int        $statusCode
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function createErrorResponse(
        string    $context,
        Throwable $throwable,
        int       $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        return new JsonResponse($this->createErrorContext($context, $throwable), $statusCode);
    }

    /**
     * @param  string     $context
     * @param  \Throwable $throwable
     *
     * @return array
     */
    private function createErrorContext(string $context, Throwable $throwable): array
    {
        $firstThrowable = $throwable;
        $errors         = [$this->formatThrowable($firstThrowable, $context)];

        while ($throwable = $throwable->getPrevious()) {
            $errors[] = $this->formatThrowable($throwable, $context);
        }

        return [
            'message' => $firstThrowable->getMessage(),
            'errors'  => $errors,
        ];
    }

    /**
     * @param  \Throwable $throwable
     * @param  string     $context
     *
     * @return array
     */
    private function formatThrowable(Throwable $throwable, string $context): array
    {
        return [
            'code'    => $throwable->getCode(),
            'message' => $throwable->getMessage(),
            'file'    => $throwable->getFile(),
            'line'    => $throwable->getLine(),
            // Hide stack trace in frontend contexts unless in development mode
            'trace'   => $context === self::CONTEXT_BACKEND || Pdk::isDevelopment()
                ? $throwable->getTrace()
                : 'Enable development mode to see stack trace.',
        ];
    }
}
