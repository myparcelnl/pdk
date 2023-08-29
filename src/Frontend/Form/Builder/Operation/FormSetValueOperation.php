<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use InvalidArgumentException;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Concern\HasFormTarget;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Contract\FormOperationWithTargetInterface;

final class FormSetValueOperation extends AbstractFormOperation implements FormOperationWithTargetInterface
{
    use HasFormTarget;

    /**
     * @var scalar
     */
    private $value;

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface $parent
     * @param  scalar                                                                       $value
     */
    public function __construct(FormOperationBuilderInterface $parent, $value)
    {
        parent::__construct($parent);

        if (! is_scalar($value)) {
            throw new InvalidArgumentException('Value must be scalar');
        }

        $this->value = $value;
    }

    /**
     * @return array
     */
    protected function createArray(): array
    {
        return ['$value' => $this->value] + parent::createArray();
    }

    /**
     * @return string
     */
    protected function getOperationKey(): string
    {
        return '$setValue';
    }
}
