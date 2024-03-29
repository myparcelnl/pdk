<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Contract;

interface ValidatorInterface extends SchemaInterface
{
    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return bool
     */
    public function validate(): bool;
}
