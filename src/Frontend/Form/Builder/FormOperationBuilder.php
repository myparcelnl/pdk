<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSingletonOperationInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\RootFormOperationBuilderInterface;
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
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface
     */
    public function afterUpdate(?callable $callback = null): FormSubOperationBuilderInterface
    {
        return $this->addBuilder(new FormAfterUpdateBuilder($this), $callback);
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $array = $this->createArray();

        $array[] = array_map(static function (FormSubOperationBuilderInterface $builder) {
            return $builder->build();
        }, $this->builders);

        return array_filter($array);
    }

    /**
     * @param  null|string     $target
     * @param  callable|scalar $valueOrCallback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
     */
    public function readOnlyWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface
    {
        return $this->addConditionalOperation(new FormReadOnlyWhenOperation($this), $target, $valueOrCallback);
    }

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
     */
    public function visibleWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface
    {
        return $this->addConditionalOperation(new FormVisibleWhenOperation($this), $target, $valueOrCallback);
    }

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface $builder
     * @param  null|callable                                                                   $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface
     */
    protected function addBuilder(
        FormSubOperationBuilderInterface $builder,
        ?callable                        $callback
    ): FormSubOperationBuilderInterface {
        $this->builders[$builder->getKey()] = $builder;

        return $this->executeCallback($builder, $callback);
    }

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSingletonOperationInterface $operation
     * @param  null|string                                                                    $target
     * @param  null                                                                           $valueOrCallback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
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
     * @param  \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSingletonOperationInterface $operation
     * @param  null|string                                                                    $target
     *
     * @return null|\MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
     */
    private function hasExistingSingletonOperation(
        FormSingletonOperationInterface $operation,
        ?string                         $target
    ): ?FormConditionInterface {
        $existingOperation = Arr::first(
            $this->operations,
            static function (FormSingletonOperationInterface $existingOperation) use ($operation) {
                return get_class($existingOperation) === get_class($operation);
            }
        );

        if ($existingOperation) {
            return $existingOperation->if($target);
        }

        return null;
    }
}
