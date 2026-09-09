<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Exception\PdkEndpointException;
use MyParcelNL\Pdk\App\Api\Contract\PdkApiInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Notification\Model\Notification;
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
        $action   = is_string($input) ? $input : $input->get('action') ?? 'unknown';
        $orderIds = is_string($input) ? null : $input->get('orderIds');

        try {
            return $this->actions
                ->setContext($context)
                ->execute($input);
        } catch (ApiException $e) {
            // In case of an ApiException, AbstractApiService has already logged the error.
            $this->addErrorNotification($e, $action, $orderIds);

            return $this->createApiErrorResponse($e);
        } catch (Throwable $e) {
            $this->addErrorNotification($e, $action, $orderIds);

            if ($e instanceof PdkEndpointException) {
                $response = $this->createErrorResponse($context, $e, $e->getStatusCode());
            } else {
                $response = $this->createErrorResponse($context, $e);
            }

            Logger::error('An exception was thrown while executing an action', [
                'action'   => $action,
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
        $data = [
            'message'    => $exception->getMessage(),
            'request_id' => $exception->getRequestId(),
            'errors'     => $exception->getErrors(),
        ];

        if (Notifications::isNotEmpty()) {
            $data['notifications'] = Notifications::all()
                ->toArrayWithoutNull();
        }

        return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
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
        $data = $this->createErrorContext($context, $throwable);

        if (Notifications::isNotEmpty()) {
            $data['notifications'] = Notifications::all()
                ->toArrayWithoutNull();
        }

        return new JsonResponse($data, $statusCode);
    }

    /**
     * Make sure every failed action reports something readable to the user. Actions that handle
     * their own errors, like exporting orders, have already added a more specific notification;
     * don't add a second one on top of it.
     *
     * @param  \Throwable            $throwable
     * @param  string                $action
     * @param  null|string|string[]  $orderIds
     *
     * @return void
     */
    private function addErrorNotification(Throwable $throwable, string $action, $orderIds = null): void
    {
        $reported = Notifications::all()
            ->firstWhere('variant', Notification::VARIANT_ERROR);

        if ($reported) {
            return;
        }

        $title   = $throwable->getMessage();
        $content = [];

        if ($throwable instanceof ApiException) {
            $title   = $throwable->getBodyMessage() ?? $title;
            $content = $throwable->getHumanMessages();
        }

        $tags = ['action' => $action];

        // The order list only renders notifications that carry the id of the order they belong to.
        if (null !== $orderIds && [] !== $orderIds) {
            $tags['orderIds'] = implode(',', preg_split('/[;,]/', implode(',', (array) $orderIds)));
        }

        Notifications::error($title, $content, Notification::CATEGORY_ACTION, $tags);
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
