<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Model;

use MyParcelNL\Pdk\App\Action\Backend\Order\ExportOrderAction;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string              $id
 * @property array                    $arguments
 * @property string                   $type
 * @property null|string              $action
 * @property null|class-string<Model> $model
 * @property null|string              $modelIdentifier
 * @property null|\DateTime           $created
 * @deprecated Audits functionality will be removed in the next major release
 */
class Audit extends Model
{
    /**
     * @deprecated Audits functionality will be removed in the next major release
     */
    public const TYPE_AUTOMATIC = 'automatic';
    /**
     * @deprecated Audits functionality will be removed in the next major release
     */
    public const TYPE_MANUAL = 'manual';

    /**
     * @var array
     */
    protected $attributes = [
        'id'              => null,
        'arguments'       => [],
        'type'            => ExportOrderAction::TYPE_MANUAL,
        'action'          => null,

        /**
         * FQCN of the model that was audited.
         *
         * @see \MyParcelNL\Pdk\App\Audit\Concern\HasAudits
         */
        'model'           => null,

        /**
         * Identifier of the model that was audited.
         */
        'modelIdentifier' => null,

        'created' => null,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'id'              => 'string',
        'type'            => 'string',
        'action'          => 'string',
        'model'           => 'string',
        'modelIdentifier' => 'string',
        'arguments'       => 'array',
        'created'         => 'createdtime',
    ];
}
