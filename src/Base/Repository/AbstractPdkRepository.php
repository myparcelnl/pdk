<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;

abstract class AbstractPdkRepository extends StorageRepository
{
    /**
     * @return string
     */
    abstract protected function getClass(): string;

    /**
     * @return string
     */
    abstract protected function getCollectionClass(): string;

    /**
     * @param  \MyParcelNL\Pdk\Base\Model\Model $model
     *
     * @return string
     */
    abstract protected function getIdentifier(Model $model): string;

    /**
     * @param  string|int $input
     *
     * @return \MyParcelNL\Pdk\Base\Model\Model
     */
    public function get($input): Model
    {
        return $this->retrieve($input);
    }

    /**
     * @param  string|int|string[]|int[] $input
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getMany($input): Collection
    {
        $collection = $this->getCollectionClass();

        return new $collection(array_map([$this, 'get'], Utils::toArray($input)));
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Model\Model $model
     *
     * @return \MyParcelNL\Pdk\Base\Model\Model
     */
    public function update(Model $model): Model
    {
        return $this->save($this->getIdentifier($model), $model);
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $collection
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function updateMany(Collection $collection): Collection
    {
        return $collection->map(function (Model $model) {
            return $this->update($model);
        });
    }

    /**
     * @param  string $key
     * @param         $data
     *
     * @return null|mixed
     */
    protected function transformData(string $key, $data)
    {
        $class  = $this->getClass();
        $result = parent::transformData($key, $data);

        return $result ? new $class($result) : null;
    }
}
