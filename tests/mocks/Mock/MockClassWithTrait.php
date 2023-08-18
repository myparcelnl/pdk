<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock;

use MyParcelNL\Pdk\Mock\Concern\MockBeConcerned;
use MyParcelNL\Pdk\Mock\Contract\MockInterface;

final class MockClassWithTrait implements MockInterface
{
    use MockBeConcerned;
}
