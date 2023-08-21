<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Collection;

use Closure;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Tests\Factory\AbstractFactory;
use MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface;

abstract class AbstractCollectionFactory extends AbstractFactory implements CollectionFactoryInterface
{
    /**
     * @var Collection<Model|Collection|FactoryInterface>
     */
    protected $entries;

    /**
     * @param  int $amount
     */
    public function __construct(int $amount = 0)
    {
        parent::__construct();

        $this->entries = new Collection();

        if ($amount > 0) {
            $this->amount($amount);
        }
    }

    /**
     * @return class-string<\MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface>
     */
    abstract protected function getModelFactory(): string;

    /**
     * @param  int $amount
     *
     * @return $this
     */
    public function amount(int $amount): CollectionFactoryInterface
    {
        $modelFactory = $this->getModelFactory();

        for ($i = 0; $i < $amount; $i++) {
            $this->push(new $modelFactory());
        }

        return $this;
    }

    /**
     * @param  array|callable $data
     *
     * @return $this
     */
    public function eachWith($data): CollectionFactoryInterface
    {
        $this->models()
            ->each(function (ModelFactoryInterface $modelFactory) use ($data) {
                if ($data instanceof Closure) {
                    $data($modelFactory);
                } else {
                    $modelFactory->with($data);
                }
            });

        return $this;
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function make(): Collection
    {
        $collection = $this->getCollection();

        return new $collection(
            $this->entries->map(function ($item) {
                return $item instanceof FactoryInterface
                    ? $item->make()
                    : $item;
            })
        );
    }

    /**
     * @param  ...mixed $item
     *
     * @return self
     */
    public function push(...$items): CollectionFactoryInterface
    {
        $this->entries->push(...$items);

        return $this;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this
     */
    public function put(string $key, $value): CollectionFactoryInterface
    {
        $this->entries->put($key, $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function store(): CollectionFactoryInterface
    {
        $this
            ->models()
            ->each(function (ModelFactoryInterface $model) {
                $model->store();
            });

        return $this;
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection<\MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface>
     */
    protected function models(): Collection
    {
        return $this->entries->filter(function ($item) {
            return $item instanceof ModelFactoryInterface;
        });
    }
}
