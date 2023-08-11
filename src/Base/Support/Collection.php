<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Collection as SdkCollection;
use Throwable;

class Collection extends SdkCollection implements StorableArrayable
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
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        parent::offsetSet($key, $value);
        $this->castItems();
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
     * @param  null|string $class
     *
     * @return self
     */
    public function setCast(?string $class): self
    {
        $this->cast = $class;
        $this->castItems();

        return $this;
    }

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return array_map(
            static function ($value) {
                if ($value instanceof StorableArrayable) {
                    return $value->toStorableArray();
                }

                if ($value instanceof Arrayable) {
                    return $value->toArray(Arrayable::SKIP_NULL);
                }

                return $value;
            },
            $this->items
        );
    }

    /**
     * @return void
     */
    protected function castItems(): void
    {
        if (! $this->cast) {
            return;
        }

        $itemsToCast = Arr::where($this->items, function ($item) {
            return ! is_a($item, $this->cast);
        });

        foreach ($itemsToCast as $key => $item) {
            try {
                $this->items[$key] = Utils::cast($this->cast, $item);
            } catch (Throwable $e) {
                // Silently fail to allow methods like pluck() to work.
            }
        }
    }
}
