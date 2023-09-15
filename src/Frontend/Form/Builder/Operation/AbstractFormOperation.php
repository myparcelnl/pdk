<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Frontend\Form\Builder\AbstractFormBuilderCore;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition;

abstract class AbstractFormOperation extends AbstractFormBuilderCore implements FormOperationInterface
{
    protected $magicMethods = ['if'];

    /**
     * @var null|string
     */
    protected     $target;

    private array $conditions = [];

    abstract protected function getOperationKey(): string;

    /**
     * @return null[]|string[]
     */
    public function createArray(): array
    {
        $array = [
            '$target' => $this->target,
        ];

        if (! empty($this->conditions)) {
            $array['$if'] = array_filter(
                array_map(static fn(FormConditionInterface $condition) => $condition->toArray(), $this->conditions)
            );
        }

        return $array;
    }

    /**
     * @param  null|string   $target
     * @param  null|callable $callable
     */
    public function if(?string $target = null, ?callable $callable = null): FormConditionInterface
    {
        $condition          = new FormCondition($this->parent, $target);
        $this->conditions[] = $condition;

        if (null !== $callable) {
            $callable($condition);
        }

        return $condition;
    }

    final public function toArray(): array
    {
        return [
            $this->getOperationKey() => Utils::filterNull(array_filter($this->createArray())),
        ];
    }
}
