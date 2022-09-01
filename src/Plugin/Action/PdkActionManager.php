<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action;

use MyParcelNL\Pdk\Api\Exception\PdkEndpointException;
use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PdkActionManager
{
    /**
     * @param  array $parameters
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function execute(array $parameters): ?Response
    {
        try {
            return $this->executeAction($parameters);
        } catch (Throwable $e) {
            return $this->getErrorResponse($e);
        }
    }

    /**
     * @param  \Throwable $throwable
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getErrorResponse(Throwable $throwable): JsonResponse
    {
        return (new JsonResponse(
            [
                'message'    => 'error :(',
                'request_id' => '',
                'errors'     => [
                    [
                        'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'code'    => $throwable->getCode(),
                        'title'   => $throwable->getMessage(),
                        'message' => $throwable->getMessage(),
                        'trace'   => Pdk::isDevelopment()
                            ? $throwable->getTrace()
                            : 'Enable development mode to see stack trace.',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST
        ));
    }

    /**
     * @param  array $parameters
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    private function executeAction(array $parameters): ?Response
    {
        $resolvedAction = $this->resolveAction($parameters['action']);

        if (! $resolvedAction) {
            return null;
        }

        /** @var \MyParcelNL\Pdk\Plugin\Action\ActionInterface $instance */
        $instance = Pdk::get($resolvedAction);

        return $instance->handle($parameters);
    }

    /**
     * @param  null|string $action
     *
     * @return null|string
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    private function resolveAction(?string $action): ?string
    {
        $allActions = new Collection(Config::get('actions'));

        $matchingEndpoint = $allActions->dataGet("endpoints.$action");
        $matchingAction   = $allActions->dataGet("optional.$action");

        if (! $matchingEndpoint && ! $matchingAction) {
            if (in_array($action, PdkActions::OPTIONAL, true)) {
                return null;
            }

            throw new PdkEndpointException("Action \"$action\" does not exist.", 422);
        }

        return $matchingEndpoint['action'] ?? $matchingAction ?? null;
    }
}
