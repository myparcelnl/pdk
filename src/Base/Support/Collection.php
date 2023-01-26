<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Sdk\src\Support\Collection as SdkCollection;
use Throwable;

class Collection extends SdkCollection implements Arrayable
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
     * @param  string $key
     * @param         $default
     *
     * @return mixed
     */
    public function dataGet(string $key, $default = null)
    {
        return (new Helpers())->data_get($this->toArray(), $key, $default);
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
     * Merge the collection with the given items where the keys and values match.
     *
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $collection
     * @param  string                                  $key
     *
     * @return self
     */
    public function mergeByKey(Collection $collection, string $key): self
    {
        $valueRetriever = $this->valueRetriever($key);
        $result         = $this->keyBy($key);

        foreach ($collection->all() as $item) {
            $keyValue = $valueRetriever($item);
            $result->put($keyValue, $item);
        }

        return $result->values();
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
