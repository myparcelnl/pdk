<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Frontend\Context;

use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Plugin\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Contract\ActionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchCheckoutContextAction implements ActionInterface
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        return Actions::execute(PdkSharedActions::FETCH_CONTEXT, ['context' => Context::ID_CHECKOUT]);
    }
}
