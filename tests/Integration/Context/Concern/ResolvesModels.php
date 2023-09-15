<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context\Concern;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Sdk\src\Support\Str;

trait ResolvesModels
{
    private $entityClassMap = [
        'ORDER'    => PdkOrder::class,
        'SHIPMENT' => Shipment::class,
        'SHOP'     => Shop::class,
        'ACCOUNT'  => Account::class,
    ];

    private $typeModifiers  = ['FIRST', 'LAST', 'CURRENT'];

    protected function resolveModel(string $identifier): ?Model
    {
        if (Str::startsWith($identifier, $this->typeModifiers)) {
            $type = Str::before($identifier, '_');
            $rest = Str::after($identifier, '_');
        } else {
            $type = null;
            $rest = $identifier;
        }

        [$modelName, $input] = explode(':', $rest);

        return $this->resolve($type, $modelName, $input);
    }

    /**
     * @param  null|string $type
     *
     * @todo implement type, resolve by FIRST or LAST
     */
    private function resolve(?string $type, string $entityName, mixed $input): ?Model
    {
        $class = $this->resolveEntityClass($entityName);

        if (! $class) {
            return null;
        }

        switch ($class) {
            case Account::class:
                return AccountSettings::getAccount();

            case Shop::class:
                return AccountSettings::getShop();

            case PdkOrder::class:
                return $this->resolveOrder($input);
        }

        return null;
    }

    private function resolveEntityClass(string $entityName): ?string
    {
        return $this->entityClassMap[$entityName] ?? null;
    }

    private function resolveOrder(mixed $input): ?PdkOrder
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $repository */
        $repository = Pdk::get(PdkOrderRepositoryInterface::class);

        return $repository->get($input);
    }
}
