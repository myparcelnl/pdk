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
            'height'       => $this->height,
            'length'       => $this->length,
            'width'        => $this->width,
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

    /**
     * @param  mixed $value
     *
     * @return self
     * @noinspection PhpUnused
     */
    protected function setHeightAttribute($value): self
    {
        return $this->setDimension('height', $value);
    }

    /**
     * @param  mixed $value
     *
     * @return self
     * @noinspection PhpUnused
     */
    protected function setLengthAttribute($value): self
    {
        return $this->setDimension('length', $value);
    }

    /**
     * @param  mixed $value
     *
     * @return self
     * @noinspection PhpUnused
     */
    protected function setWidthAttribute($value): self
    {
        return $this->setDimension('width', $value);
    }

    /**
     * Dimensions are optional and intentionally not validated: 0 and -1 are passed through to the
     * API unchanged. An empty field must not become 0 though, and the admin's number input emits
     * an empty string when cleared, which the int cast would silently turn into 0. Anything
     * non-numeric therefore becomes null, so the key is omitted from storage and from the request.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return self
     */
    private function setDimension(string $key, $value): self
    {
        $this->attributes[$key] = is_numeric($value) ? (int) $value : null;

        return $this;
    }
}
