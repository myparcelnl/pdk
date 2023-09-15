<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSingletonOperationInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\RootFormOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormDisabledWhenOperation;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormReadOnlyWhenOperation;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormVisibleWhenOperation;

final class FormOperationBuilder extends AbstractFormOperationBuilder implements RootFormOperationBuilderInterface
{
    protected $builders = [];

    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param  null|callable $callback
     */
    public function afterUpdate(?callable $callback = null): FormSubOperationBuilderInterface
    {
        return $this->addBuilder(new FormAfterUpdateBuilder($this), $callback);
    }

    public function build(): array
    {
        $array = $this->createArray();

        $array[] = array_map(static fn(FormSubOperationBuilderInterface $builder) => $builder->createArray(),
            $this->builders);

        return array_filter($array);
    }

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     */
    public function disabledWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface
    {
        return $this->addConditionalOperation(new FormDisabledWhenOperation($this), $target, $valueOrCallback);
    }

    /**
     * @param  null|string     $target
     * @param  callable|scalar $valueOrCallback
     */
    public function readOnlyWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface
    {
        return $this->addConditionalOperation(new FormReadOnlyWhenOperation($this), $target, $valueOrCallback);
    }

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     */
    public function visibleWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface
    {
        return $this->addConditionalOperation(new FormVisibleWhenOperation($this), $target, $valueOrCallback);
    }

    /**
     * @template T of FormSubOperationBuilderInterface
     * @param  T             $builder
     * @param  null|callable $callback
     *
     * @return T
     */
    protected function addBuilder(
        FormSubOperationBuilderInterface $builder,
        ?callable                        $callback
    ): FormSubOperationBuilderInterface {
        $this->builders[$builder->getKey()] = $builder;

        return $this->executeCallback($builder, $callback);
    }

    /**
     * @param  null|string     $target
     * @param  callable|scalar $valueOrCallback
     */
    protected function addConditionalOperation(
        FormSingletonOperationInterface $operation,
        ?string                         $target = null,
                                        $valueOrCallback = null
    ): FormConditionInterface {
        if (! is_string($valueOrCallback) && is_callable($valueOrCallback)) {
            $value    = null;
            $callback = $valueOrCallback;
        } else {
            $value    = $valueOrCallback;
            $callback = null;
        }

        $if = $this->hasExistingSingletonOperation($operation, $target);

        $resolvedIf = $if ?? $this
            ->addOperation($operation, $callback)
            ->if($target);

        if (isset($value)) {
            $resolvedIf->eq($value);
        }

        return $resolvedIf;
    }

    /**
     * @param  null|string $target
     */
    private function hasExistingSingletonOperation(
        FormSingletonOperationInterface $operation,
        ?string                         $target
    ): ?FormConditionInterface {
        $existingOperation = Arr::first(
            $this->operations,
            static fn(FormSingletonOperationInterface $existingOperation
            ) => $existingOperation::class === $operation::class
        );

        if ($existingOperation) {
            return $existingOperation->if($target);
        }

        return null;
    }
}
