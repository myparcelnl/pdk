<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Validation\Contract\ValidatorInterface;

interface OrderValidatorInterface extends ValidatorInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return self
     */
    public function setOrder(PdkOrder $order): self;
}
