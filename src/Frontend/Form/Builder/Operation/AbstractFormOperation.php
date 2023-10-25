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
    protected $target;

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @return string
     */
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
                array_map(static function (FormConditionInterface $condition) {
                    return $condition->toArray();
                }, $this->conditions)
            );
        }

        return $array;
    }

    /**
     * @param  null|string   $target
     * @param  null|callable $callable
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
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

    /**
     * @param  null|int $flags
     *
     * @return array
     */
    final public function toArray(?int $flags = null): array
    {
        return [
            $this->getOperationKey() => Utils::filterNull(array_filter($this->createArray())),
        ];
    }
}
