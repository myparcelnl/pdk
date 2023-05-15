<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Cart\Repository\AbstractPdkCartRepository;

class MockPdkCartRepository extends AbstractPdkCartRepository
{
    public function get($input): PdkCart
    {
        return new PdkCart();
    }
}
