<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use Throwable;

class Collection extends \MyParcelNL\Sdk\src\Support\Collection implements Arrayable
{
    /**
     * Defines a class items should be cast into.
     *
     * @var null|class-string
     */
    protected $cast;

    /**
     * @param  mixed $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);
        $this->castItems();
    }

    /**
     * Map the values into a new class.
     *
     * @param  string $class
     *
     * @return self
     */
    public function mapInto($class): self
    {
        return $this->map(function ($value, $key) use ($class) {
            return Utils::cast($class, $value, $key);
        });
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param  mixed $values [optional]
     *
     * @return $this
     */
    public function push(...$values): self
    {
        parent::push(...$values);
        $this->castItems();
        return $this;
    }

    /**
     * @return void
     */
    protected function castItems(): void
    {
        if (! $this->cast) {
            return;
        }

        $noItemsToCast = $this->every(function ($item) {
            return is_a($item, $this->cast);
        });

        if ($noItemsToCast) {
            return;
        }

        foreach ($this->items as $key => $item) {
            try {
                $this->items[$key] = Utils::cast($this->cast, $item);
            } catch (Throwable $e) {
                // Silently fail to allow methods like pluck() to work.
            }
        }
    }
}
