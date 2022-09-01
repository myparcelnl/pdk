<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Plugin\Action\PdkActionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PdkEndpoint
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Action\PdkActionManager
     */
    private $manager;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Action\PdkActionManager $manager
     */
    public function __construct(PdkActionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param  string $action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \Exception
     */
    public function call(string $action): Response
    {
        $request = Request::createFromGlobals();
        $request->query->set('action', $action);

        $parameters = $request->query->all();

        return $this->manager->execute($parameters) ?? (new Response())->setStatusCode(Response::HTTP_I_AM_A_TEAPOT);
    }
}
