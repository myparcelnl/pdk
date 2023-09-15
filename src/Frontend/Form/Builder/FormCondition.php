<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Frontend\Form\Builder\Concern\HasFormOperationBuilderParent;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;

/**
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface $or
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface $and
 */
final class FormCondition extends AbstractFormBuilderCore implements FormConditionInterface
{
    use HasFormOperationBuilderParent;

    protected $magicMethods = ['and', 'or'];

    /**
     * @var string
     */
    protected $matcher;

    /**
     * @var scalar|scalar[]
     */
    protected     $value;

    private array $ands = [];

    private array $ors  = [];

    /**
     * @param  null|string $target
     */
    public function __construct(FormOperationBuilderInterface $parent, protected ?string $target = null)
    {
        parent::__construct($parent);
    }

    /**
     * @param  null|string $target
     *
     * @return self
     */
    public function and(?string $target = null): FormConditionInterface
    {
        $and = new self($this->parent, $target ?? $this->target);

        if (empty($this->ands)) {
            $this->ands[] = $this;
        }

        $this->ands[] = $and;

        return $and;
    }

    /**
     * @param  scalar $value
     *
     * @return $this
     */
    public function eq($value): FormConditionInterface
    {
        $this->matcher = '$eq';
        $this->value   = $value;

        return $this;
    }

    /**
     * @param  scalar $value
     *
     * @return $this
     */
    public function gt($value): FormConditionInterface
    {
        $this->matcher = '$gt';
        $this->value   = $value;

        return $this;
    }

    /**
     * @param  scalar $value
     *
     * @return $this
     */
    public function gte($value): FormConditionInterface
    {
        $this->matcher = '$gte';
        $this->value   = $value;

        return $this;
    }

    /**
     * @param  scalar[] $value
     *
     * @return $this
     */
    public function in(array $value): FormConditionInterface
    {
        $this->matcher = '$in';
        $this->value   = $value;

        return $this;
    }

    /**
     * @param  scalar $value
     *
     * @return $this
     */
    public function lt($value): FormConditionInterface
    {
        $this->matcher = '$lt';
        $this->value   = $value;

        return $this;
    }

    /**
     * @param  scalar $value
     *
     * @return $this
     */
    public function lte($value): FormConditionInterface
    {
        $this->matcher = '$lte';
        $this->value   = $value;

        return $this;
    }

    /**
     * @param  scalar $value
     *
     * @return $this
     */
    public function ne($value): FormConditionInterface
    {
        $this->matcher = '$ne';
        $this->value   = $value;

        return $this;
    }

    /**
     * @param  scalar[] $value
     *
     * @return $this
     */
    public function nin(array $value): FormConditionInterface
    {
        $this->matcher = '$nin';
        $this->value   = $value;

        return $this;
    }

    public function toArray(): array
    {
        $array = array_filter([
            '$and' => $this->createArrays($this->ands),
            '$or'  => $this->createArrays($this->ors),
        ], static fn($item) => ! empty($item));

        if (! empty($array)) {
            return $array;
        }

        return $this->createSingleArray();
    }

    /**
     * @return self
     */
    protected function or(): FormConditionInterface
    {
        $or = new self($this->parent, $this->target);

        if (empty($this->ors)) {
            $this->ors[] = $this;
        }

        $this->ors[] = $or;

        return $or;
    }

    private function createArrays(array $array): array
    {
        return array_map(static fn(self $item) => $item->createSingleArray(), $array);
    }

    private function createSingleArray(): array
    {
        return Utils::filterNull([
            '$target'      => $this->target,
            $this->matcher => $this->value,
        ]);
    }
}
