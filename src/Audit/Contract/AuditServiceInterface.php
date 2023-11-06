<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Contract;

use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Model\Audit;

interface AuditServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Audit\Model\Audit $audit
     *
     * @return \MyParcelNL\Pdk\Audit\Model\Audit
     */
    public function add(Audit $audit): Audit;

    /**
     * @param  string $model
     * @param  string $identifier
     *
     * @return \MyParcelNL\Pdk\Audit\Collection\AuditCollection
     */
    public function allByModel(string $model, string $identifier): AuditCollection;
}
