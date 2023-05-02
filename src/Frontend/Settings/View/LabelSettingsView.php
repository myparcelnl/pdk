<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Plugin\Admin\View\PrintOptionsView;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class LabelSettingsView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Admin\View\PrintOptionsView
     */
    private $printOptionsView;

    public function __construct(PrintOptionsView $printOptionsView)
    {
        $this->printOptionsView = $printOptionsView;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function createElements(): FormElementCollection
    {
        $fields = [
            new InteractiveElement(LabelSettings::DESCRIPTION, Components::INPUT_TEXT),
            new InteractiveElement(LabelSettings::PROMPT, Components::INPUT_TOGGLE),
        ];

        $elements = $this->printOptionsView->getElements();

        if ($elements) {
            $elements = $this->updateElements($elements);

            foreach ($elements->all() as $element) {
                /** @var \MyParcelNL\Pdk\Frontend\Form\PlainElement $element */
                $fields[] = $element;
            }
        }

        return new FormElementCollection($fields);
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return LabelSettings::ID;
    }
}
