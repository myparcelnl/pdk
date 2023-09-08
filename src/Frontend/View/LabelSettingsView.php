<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Frontend\Form\Element\SettingsDivider;
use MyParcelNL\Pdk\Frontend\Form\Element\TextInput;
use MyParcelNL\Pdk\Frontend\Form\Element\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
final class LabelSettingsView extends NewAbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Frontend\View\PrintOptionsView
     */
    private $printOptionsView;

    /**
     * @param  \MyParcelNL\Pdk\Frontend\View\PrintOptionsView $printOptionsView
     */
    public function __construct(PrintOptionsView $printOptionsView)
    {
        parent::__construct();

        $this->printOptionsView = $printOptionsView;
    }

    protected function addElements(): void
    {
        $this->formBuilder->add(
            new TextInput(LabelSettings::DESCRIPTION),
            new ToggleInput(LabelSettings::PROMPT),
            new SettingsDivider($this->label('defaults')),
        );

        $this->formBuilder->add(...$this->printOptionsView->all());
    }

    protected function getPrefix(): string
    {
        return LabelSettings::ID;
    }
}
