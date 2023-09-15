<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Validation\Contract\ValidatorInterface;

interface OrderValidatorInterface extends ValidatorInterface
{
    public function setOrder(PdkOrder $order): self;
}
