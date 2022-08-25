<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action;

use Symfony\Component\HttpFoundation\Response;

interface ActionInterface
{
    /**
     * @param  array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(array $parameters): Response;
}
