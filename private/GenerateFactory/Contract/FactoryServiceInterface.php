<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\GenerateFactory\Contract;

use MyParcelNL\Pdk\Console\Contract\HasCommandContextInterface;
use MyParcelNL\Pdk\Console\Types\Shared\Model\ClassDefinition;

interface FactoryServiceInterface extends HasCommandContextInterface
{
    public function generate(ClassDefinition $definition): void;
}
