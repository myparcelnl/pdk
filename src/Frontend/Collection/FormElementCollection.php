<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;

/**
 * @property InteractiveElement[]|PlainElement[] $items
 */
class FormElementCollection extends Collection
{
    /**
     * @var string
     */
    protected $settingsSection;

    /**
     * @return string
     */
    public function getSettingsSection(): string
    {
        return $this->settingsSection;
    }

    /**
     * @param  string $id
     *
     * @return $this
     */
    public function setSettingsSection(string $id): self
    {
        $this->settingsSection = $id;

        return $this;
    }
}

