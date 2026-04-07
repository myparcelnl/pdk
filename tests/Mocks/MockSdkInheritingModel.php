<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\SdkBackedModel;

/**
 * A PDK Model that extends SdkBackedModel backed by a MockSdkModel instance.
 *
 * @property string|null $title      Native PDK attribute
 * @property string|null $name       Native PDK attribute (overlaps with SDK model's 'name' property, PDK wins)
 * @property string|null $firstName  Inherited from SDK model
 * @property string|null $lastName   Inherited from SDK model
 * @property int|null    $age        Inherited from SDK model
 */
class MockSdkInheritingModel extends SdkBackedModel
{
    protected $sdkModelClass = MockSdkModel::class;

    protected $attributes = [
        'title' => null,
        'name'  => null,
    ];

    protected $casts = [
        'title' => 'string',
        'name'  => 'string',
    ];
}
