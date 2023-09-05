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
     * @var string
     */
    private $prop;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(?FormOperationBuilderInterface $parent, string $prop, $value)
    {
        parent::__construct($parent);
        $this->prop  = $prop;
        $this->value = $value;
    }

    /**
     * @return array
     */
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
