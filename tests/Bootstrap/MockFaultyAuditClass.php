<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Audit\Concern\HasAudits;
use MyParcelNL\Pdk\Base\Model\Model;

final class MockFaultyAuditClass extends Model
{
    use HasAudits;

    protected $attributes = [
        'randomIdentifier' => null,
    ];

    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->initializeHasAudits();
    }
}
