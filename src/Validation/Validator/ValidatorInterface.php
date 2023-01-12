<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

interface ValidatorInterface
{
    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return array
     */
    public function getSchema(): array;

    /**
     * @return bool
     */
    public function validate(): bool;
}
