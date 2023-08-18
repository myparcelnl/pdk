<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Repository;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;

final class MockPdkCartRepository extends AbstractPdkCartRepository
{
    public function get($input): PdkCart
    {
        return new PdkCart();
    }
}
