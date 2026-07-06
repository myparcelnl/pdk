<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Frontend\Context;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds the context needed by the checkout front-end.
 *
 * This is the only context-fetch action exposed on the public (unauthenticated) frontend endpoint.
 * It deliberately builds the checkout context directly via the ContextService and hard-codes the
 * context id to {@see Context::ID_CHECKOUT}, so an anonymous caller can never widen it.
 */
class FetchCheckoutContextAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \MyParcelNL\Pdk\Context\Contract\ContextServiceInterface
     */
    protected $contextService;

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface $cartRepository
     * @param  \MyParcelNL\Pdk\Context\Contract\ContextServiceInterface     $contextService
     */
    public function __construct(
        PdkCartRepositoryInterface $cartRepository,
        ContextServiceInterface    $contextService
    ) {
        $this->cartRepository = $cartRepository;
        $this->contextService = $contextService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $context = $this->contextService->createContexts(
            [Context::ID_CHECKOUT],
            ['cart' => $this->cartRepository->get($request->get('cart'))]
        );

        return new JsonResponse(['context' => [$context->toArrayWithoutNull()]]);
    }
}
