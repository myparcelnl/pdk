<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Settings;

use MyParcelNL\Pdk\Plugin\Action\Backend\Order\AbstractOrderAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductSettingsAction extends AbstractOrderAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $data = $request->getContent();

        return (new Response())->setStatusCode(Response::HTTP_OK);
    }
}

