<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Frontend\Form\Builder\Concern\HasFormOperationBuilderParent;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;

/**
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition        $or
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition        $and
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder $then
 */
final class FormCondition extends AbstractFormBuilderCore
{
    use HasFormOperationBuilderParent;

    protected $magicMethods = ['and', 'or', 'then'];

    /**
     * @var string
     */
    protected $matcher;

    /**
     * @var null|string
     */
    protected $target;

    /**
     * @var scalar
     */
    protected $value;

    /**
     * @var array
     */
    private $ands = [];

    /**
     * @var array
     */
    private $ors = [];

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface $parent
     * @param  null|string                                                                  $target
     */
    public function __construct(FormOperationBuilderInterface $parent, ?string $target = null)
    {
        parent::__construct($parent);
        $this->target = $target;
    }

    /**
     * @param  null|string $target
     *
     * @return self
     */
    public function and(?string $target = null): self
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
    public function eq($value): self
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
    public function gt($value): self
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
    public function gte($value): self
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
    public function in(array $value): self
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
    public function lt($value): self
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
    public function lte($value): self
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
    public function ne($value): self
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
    public function nin(array $value): self
    {
        $this->matcher = '$nin';
        $this->value   = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = array_filter([
            '$and' => $this->createArrays($this->ands),
            '$or'  => $this->createArrays($this->ors),
        ], static function ($item) {
            return ! empty($item);
        });

        if (! empty($array)) {
            return $array;
        }

        return $this->createSingleArray();
    }

    /**
     * @return self
     */
    protected function or(): self
    {
        $or = new self($this->parent, $this->target);

        if (empty($this->ors)) {
            $this->ors[] = $this;
        }

        $this->ors[] = $or;

        return $or;
    }

    /**
     * @param  array $array
     *
     * @return array
     */
    private function createArrays(array $array): array
    {
        return array_map(static function (self $item) {
            return $item->createSingleArray();
        }, $array);
    }

    /**
     * @return array
     */
    private function createSingleArray(): array
    {
        return Utils::filterNull([
            '$target'      => $this->target,
            $this->matcher => $this->value,
        ]);
    }
}
