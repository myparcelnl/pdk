<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Contract;

use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;

interface PdkAuditRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all audits from the database.
     */
    public function all(): AuditCollection;

    /**
     * Store a single audit in the database of the platform.
     */
    public function store(Audit $audit): void;
}
