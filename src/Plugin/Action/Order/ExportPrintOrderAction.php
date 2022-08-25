<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Order;

use Symfony\Component\HttpFoundation\Response;

class ExportPrintOrderAction extends ExportOrderAction
{
    /**
     * @param  array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(array $parameters): Response
    {
        return parent::handle(['print' => true] + $parameters);
    }
}
