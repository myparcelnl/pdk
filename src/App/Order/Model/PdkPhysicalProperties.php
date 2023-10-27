<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property null|int $height
 * @property null|int $length
 * @property null|int $width
 * @property int      $initialWeight
 * @property int      $manualWeight
 * @property int      $totalWeight
 */
class PdkPhysicalProperties extends Model
{
    protected $attributes = [
        'height'        => null,
        'length'        => null,
        'width'         => null,

        /**
         * Base weight.
         */
        'initialWeight' => 0,

        /**
         * Optional manual override of the initial weight.
         */
        'manualWeight'  => TriStateService::INHERIT,

        /**
         * Calculated automatically based on the initial weight and manual weight.
         */
        'totalWeight'   => 0,
    ];

    protected $casts      = [
        'height'        => 'int',
        'length'        => 'int',
        'width'         => 'int',
        'initialWeight' => 'int',
        'manualWeight'  => 'int',
        'totalWeight'   => 'int',
    ];

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return Utils::filterNull([
            'manualWeight' => TriStateService::INHERIT === $this->manualWeight ? null : $this->manualWeight,
        ]);
    }

    /**
     * @return int
     * @noinspection PhpUnused
     */
    protected function getTotalWeightAttribute(): int
    {
        /** @var TriStateService $triStateService */
        $triStateService = Pdk::get(TriStateService::class);

        return $triStateService->resolve($this->manualWeight, $this->initialWeight);
    }
}
