<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Repository\AbstractPdkRepository;

/**
 * @method PdkOrder get($input)
 * @method PdkOrderCollection getMany($input)
 * @method PdkOrder update(PdkOrder $order)
 * @method PdkOrderCollection updateMany(PdkOrderCollection $collection)
 */
class PdkOrderRepository extends AbstractPdkRepository
{
    //    public function get($input): Model
    //    {
    //        return $this->retrieve($input);
    //    }
    //
    //    public function getMany($orderIds): Collection
    //    {
    //        return new PdkOrderCollection(array_map([$this, 'get'], Utils::toArray($orderIds)));
    //    }

    //    public function update(PdkOrder $model): PdkOrder
    //    {
    //        return $this->save($model->externalIdentifier, $model);
    //    }
    //
    //    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection
    //    {
    //        return $collection->map(function (PdkOrder $order) {
    //            return $this->update($order);
    //        });
    //    }

    /**
     * @return string
     */
    protected function getClass(): string
    {
        return PdkOrder::class;
    }

    /**
     * @return string
     */
    protected function getCollectionClass(): string
    {
        return PdkOrderCollection::class;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $model
     *
     * @return string
     */
    protected function getIdentifier(Model $model): string
    {
        return $model->externalIdentifier;
    }
    //    /**
    //     * @param  string $key
    //     * @param         $data
    //     *
    //     * @return null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder
    //     */
    //    protected function transformData(string $key, $data)
    //    {
    //        return $data ? new PdkOrder($data) : null;
    //    }
}
