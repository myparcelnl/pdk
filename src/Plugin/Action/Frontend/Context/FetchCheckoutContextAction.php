<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Frontend\Context;

use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Plugin\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Contract\ActionInterface;
use MyParcelNL\Pdk\Plugin\Contract\PdkCartRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchCheckoutContextAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Contract\PdkCartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Contract\PdkCartRepositoryInterface $cartRepository
     */
    public function __construct(PdkCartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        return Actions::execute(PdkSharedActions::FETCH_CONTEXT, [
            'context' => Context::ID_CHECKOUT,
            'cart'    => $this->cartRepository->get($request->get('cart')),
        ]);
    }
}
