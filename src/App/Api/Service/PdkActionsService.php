<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Exception\PdkEndpointException;
use MyParcelNL\Pdk\App\Action\Backend\Order\ExportOrderAction;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PdkActionsService implements PdkActionsServiceInterface
{
    /**
     * @var string
     */
    private $context = PdkEndpoint::CONTEXT_BACKEND;

    /**
     * @param  string|\Symfony\Component\HttpFoundation\Request $action
     * @param  array                                            $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    public function execute($action, array $parameters = []): Response
    {
        $request = $this->createRequest($action, $parameters);

        $actionClass = $this->resolveAction($request);

        /** @var \MyParcelNL\Pdk\App\Action\Contract\ActionInterface $action */
        $action = Pdk::get($actionClass);

        return $action->handle($request);
    }

    /**
     * @param        $action
     * @param  array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    public function executeAutomatic($action, array $parameters = []): Response
    {
        return $this->execute($action, array_replace($parameters, ['actionType' => ExportOrderAction::TYPE_AUTOMATIC]));
    }

    /**
     * @param  string $context
     *
     * @return $this
     */
    public function setContext(string $context): PdkActionsServiceInterface
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @param        $input
     * @param  array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function createRequest($input, array $parameters = []): Request
    {
        if ($input instanceof Request) {
            if (empty($input->get('action'))) {
                throw new InvalidArgumentException('Required parameter "action" is missing.');
            }

            return $input;
        }

        if (is_string($input)) {
            $request = Request::createFromGlobals();

            foreach ($parameters as $key => $value) {
                $request->query->set($key, $value);
            }

            $request->query->set('action', $input);

            return $request;
        }

        throw new InvalidArgumentException('Input must be a string or a Request object.');
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Api\Exception\PdkEndpointException
     */
    private function resolveAction(Request $request): string
    {
        $action = $request->get('action');

        if (! $this->context || ! in_array($this->context, PdkEndpoint::CONTEXTS, true)) {
            throw new PdkEndpointException('Context is invalid.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $actions = new Collection(Config::get('actions'));

        $match       = $actions->dataGet("$this->context.$action") ?? $actions->dataGet("shared.$action");
        $actionClass = $match['action'] ?? null;

        if (! $actionClass || ! class_exists($actionClass)) {
            throw new PdkEndpointException("Action \"$action\" does not exist.", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $actionClass;
    }
}
