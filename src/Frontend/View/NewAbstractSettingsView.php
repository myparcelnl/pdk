<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Contract\InteractiveElementBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\FormBuilder;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @todo rename to AbstractSettingsView when all forms are converted
 */
abstract class NewAbstractSettingsView implements Arrayable
{
    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\FormBuilder
     */
    protected $formBuilder;

    /**
     * @var bool
     */
    private $initialized = false;

    public function __construct()
    {
        $this->formBuilder = new FormBuilder([$this->getRootPrefix(), $this->getPrefix()]);
    }

    /**
     * @return void
     */
    abstract protected function addElements(): void;

    /**
     * @return array
     */
    public function all(): array
    {
        $this->initialize();

        $filtered = array_filter($this->formBuilder->all(), function (ElementBuilderInterface $elementBuilder): bool {
            $name = $elementBuilder->getName();

            if (! $name) {
                return true;
            }

            return $this->isNotDisabled($name);
        });

        return array_values($filtered);
    }

    /**
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        return [
            'id'          => $this->getPrefix(),
            'title'       => $this->label('title'),
            'description' => $this->label('description'),
            'elements'    => $this->getElements(),
            'children'    => $this->getChildren(),
        ];
    }

    /**
     * @param  string ...$parts
     *
     * @return string
     */
    protected function createLabel(string ...$parts): string
    {
        return Str::snake(implode('_', $parts));
    }

    /**
     * @return null|array
     */
    protected function getChildren(): ?array
    {
        return null;
    }

    /**
     * @return null|array
     */
    protected function getElements(): ?array
    {
        $this->updateElements(new FormElementCollection($this->all()));

        return $this->formBuilder
            ->build()
            ->toArrayWithoutNull();
    }

    /**
     * @return string
     */
    protected function getPrefix(): string
    {
        return '';
    }

    protected function getRootPrefix(): string
    {
        return 'settings';
    }

    /**
     * @param  string $name
     *
     * @return bool
     */
    protected function isNotDisabled(string $name): bool
    {
        /** @var array[] $disabledSettings */
        $disabledSettings = Pdk::get('disabledSettings');
        $category         = $disabledSettings[$this->getPrefix()] ?? [];

        return ! in_array($name, $category, true);
    }

    /**
     * @param  string ...$parts
     *
     * @return string
     */
    protected function label(string ...$parts): string
    {
        return $this->createLabel($this->getRootPrefix(), $this->getPrefix(), ...$parts);
    }

    /**
     * @param  \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection $elements
     *
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function updateElements(FormElementCollection $elements): FormElementCollection
    {
        return $elements->map(function (ElementBuilderInterface $builder): ElementInterface {
            if ($builder instanceof InteractiveElementBuilderInterface) {
                $label       = $this->label($builder->getName());
                $description = "{$label}_description";

                $builder->withProp('label', $label);

                if (Language::hasTranslation($description)) {
                    $builder->withProp('description', $description);
                }
            }

            return $builder->make();
        });
    }

    /**
     * @return void
     */
    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;
        $this->addElements();
    }
}
