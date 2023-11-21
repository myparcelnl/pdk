<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static Audit add(Audit $audit)
 * @method static AuditCollection allByModel(string $model, string $identifier)
 * @see \MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface
 */
final class Audits extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuditServiceInterface::class;
    }
}
