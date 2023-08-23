<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\BuilderInterface;

/**
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder $then
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition        $if
 */
class FormSetOperation extends AbstractBuilderCore
{
    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition[]
     */
    protected $conditions = [];

    /**
     * @var string[]
     */
    protected $magicMethods = ['if', 'then'];

    /**
     * @var null|string
     */
    protected $target;

    /**
     * @var scalar
     */
    protected $value;

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\BuilderInterface $parent
     * @param  scalar                                                          $value
     */
    public function __construct(BuilderInterface $parent, $value)
    {
        parent::__construct($parent);
        $this->value = $value;
    }

    /**
     * @param  null|string   $target
     * @param  null|callable $callable
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition
     */
    public function if(?string $target = null, ?callable $callable = null): FormCondition
    {
        $condition          = new FormCondition($this, $target);
        $this->conditions[] = $condition;

        if (null !== $callable) {
            $callable($condition);
        }

        return $condition;
    }

    /**
     * @param  null|string $target
     *
     * @return $this
     */
    public function on(?string $target): FormSetOperation
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return array
     */
    protected function createArray(): array
    {
        $ifConditions = array_map(static function (FormCondition $condition) {
            return $condition->createArray();
        }, $this->conditions);

        $array = [
            '$target' => $this->target,
            '$value'  => $this->value,
        ];

        if (! empty($this->conditions)) {
            $array['$if'] = $ifConditions;
        }

        return ['$set' => Utils::filterNull($array)];
    }
}
