<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string              $id
 * @property array                    $arguments
 * @property string                   $type
 * @property null|string              $action
 * @property null|class-string<Model> $model
 * @property null|string              $modelIdentifier
 * @property null|\DateTime           $created
 */
class Audit extends Model
{
    public const TYPE_AUTOMATIC = 'automatic';
    public const TYPE_MANUAL    = 'manual';

    /**
     * @var array
     */
    protected $attributes = [
        'id'              => null,
        'arguments'       => [],
        'type'            => self::TYPE_MANUAL,
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
