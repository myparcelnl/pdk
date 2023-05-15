<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Frontend\Context;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Actions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchCheckoutContextAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface $cartRepository
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
