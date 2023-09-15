<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Contract;

interface ValidatorInterface extends SchemaInterface
{
    public function getErrors(): array;

    public function validate(): bool;
}
