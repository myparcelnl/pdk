<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Concern\HasFormTarget;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Contract\FormOperationWithTargetInterface;

final class FormSetPropOperation extends AbstractFormOperation implements FormOperationWithTargetInterface
{
    use HasFormTarget;

    /**
     * @param  mixed $value
     */
    public function __construct(?FormOperationBuilderInterface $parent, private readonly string $prop, private $value)
    {
        parent::__construct($parent);
    }

    public function createArray(): array
    {
        return [
                '$prop'  => $this->prop,
                '$value' => $this->value,
            ] + parent::createArray();
    }

    protected function getOperationKey(): string
    {
        return '$setProp';
    }
}
