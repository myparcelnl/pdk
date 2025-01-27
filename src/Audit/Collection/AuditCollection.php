<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Collection;

use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\Audit\Model\Audit[] $items
 * @deprecated Audits functionality will be removed in the next major release
 */
class AuditCollection extends Collection
{
    protected $cast = Audit::class;

    /**
     * @return self
     * @deprecated Audits functionality will be removed in the next major release
     */
    public function automatic(): self
    {
        return $this->where('type', Audit::TYPE_AUTOMATIC);
    }

    /**
     * @param  string $action
     *
     * @return bool
     * @deprecated Audits functionality will be removed in the next major release
     */
    public function hasAction(string $action): bool
    {
        return $this->contains('action', $action);
    }
}
