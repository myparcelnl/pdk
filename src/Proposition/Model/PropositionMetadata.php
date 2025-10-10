<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * PropositionMetadata represents metadata for a proposition, including its ID, key, name, and short name.
 *
 * @property int|null $id The unique identifier for the proposition. (currently auto-incremented integers)
 * @property string|null $key The machine readable name associated with the proposition. (Ex. "myparcel-nl")
 * @property string|null $name The name of the proposition. (Ex. "MyParcel Nederland")
 * @property string|null $shortName A shorter version of the proposition name. (Ex. "MyParcel")
 * @package MyParcelNL\Pdk\Proposition
 */
class PropositionMetadata extends Model
{
    protected $attributes = [
        'id' => null,
        'key' => null,
        'name' => null,
        'shortName' => null
    ];

    protected $casts = [
        'id' => 'int',
        'key' => 'string',
        'name' => 'string',
        'shortName' => 'string',
    ];
}
