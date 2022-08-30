<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Support\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;

abstract class AbstractView implements Arrayable
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    abstract protected function getFields(): Collection;

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (! $this->fields) {
            $this->fields = $this
                ->getFields()
                ->map(function ($item) {
                    $this->validate($item);

                    $item['type'] = Utils::classBasename($item['class']);
                    unset($item['class']);

                    return $item;
                })
                ->toArray();
        }

        return $this->fields;
    }

    /**
     * @param  array $item
     */
    protected function validate(array $item): void
    {
        if (isset($item['type'])) {
            throw new InvalidArgumentException('Property "type" can not be manually set. Use "class" instead.');
        }

        if (! isset($item['name'], $item['class'])) {
            throw new InvalidArgumentException(sprintf('Fields "name" and "class" are required in %s', $item['class']));
        }
    }
}
