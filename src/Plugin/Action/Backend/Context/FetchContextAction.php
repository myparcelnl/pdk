<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Context;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Plugin\Action\ActionInterface;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchContextAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface
     */
    private $contextService;

    public function __construct(ContextServiceInterface $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return string[]
     */
    public function getContexts(Request $request): array
    {
        $contexts        = [];
        $requestContexts = $request->get('contexts');

        if ($requestContexts) {
            $contexts += explode(',', $requestContexts) ?: [];
        }

        return $contexts;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function handle(Request $request): Response
    {
        $context = $this->contextService->createContexts($this->getContexts($request));

        return new JsonResponse(['context' => [$context->toArrayWithoutNull()]]);
    }
}
