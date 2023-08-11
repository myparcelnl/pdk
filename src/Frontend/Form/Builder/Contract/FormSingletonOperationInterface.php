<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

/**
 * A form operation that can not have multiple instances in the same builder.
 */
interface FormSingletonOperationInterface extends FormOperationInterface
{
}
