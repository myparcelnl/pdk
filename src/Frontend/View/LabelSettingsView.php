<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class LabelSettingsView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Frontend\View\PrintOptionsView
     */
    private $printOptionsView;

    public function __construct(PrintOptionsView $printOptionsView)
    {
        $this->printOptionsView = $printOptionsView;
    }

    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        $fields = [
            new InteractiveElement(LabelSettings::DESCRIPTION, Components::INPUT_TEXT),
            new InteractiveElement(LabelSettings::PROMPT, Components::INPUT_TOGGLE),

            new SettingsDivider($this->getSettingKey('defaults')),
        ];

        $elements = $this->printOptionsView->getElements();

        if ($elements) {
            $elements = $this->updateElements($elements);

            foreach ($elements->all() as $element) {
                /** @var \MyParcelNL\Pdk\Frontend\Form\PlainElement $element */
                $fields[] = $element;
            }
        }

        return $fields;
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return LabelSettings::ID;
    }
}
