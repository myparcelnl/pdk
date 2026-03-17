<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Concern;

use InvalidArgumentException;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;

/**
 * Provides a normalising setter and a repository-backed getter for a single "carrier" attribute.
 *
 * The raw attribute is always stored as a plain carrier-name string (or null).
 * The getter resolves the stored name to a full Carrier model on demand, falling back
 * to the proposition default carrier when no name is stored.
 */
trait HasCarrierAttribute
{
    /**
     * Always resolves a fresh Carrier from the repository so capability data is never stale.
     *
     * - No carrier set → returns the proposition default carrier.
     * - Carrier name set but not found in the repository → throws (repository findOrFail).
     *
     * Reads directly from $this->attributes to avoid re-entering getAttribute() which would
     * cause infinite recursion (getAttribute → transformModelValue → mutateAttribute → here).
     *
     * @return \MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function getCarrierAttribute(): Carrier
    {
        $carrierName = $this->attributes['carrier'];

        if (! $carrierName) {
            return Pdk::get(PropositionService::class)->getDefaultCarrier();
        }

        return Pdk::get(CarrierRepositoryInterface::class)->findOrFail($carrierName);
    }

    /**
     * Normalise any incoming carrier value to its string identifier and store it.
     * Only a Carrier model (reduces to its carrier name), a plain string name, or
     * null are accepted. An array in Carrier-model-array form is also handled.
     * Passing any other type is a programming error and throws immediately.
     *
     * @param  string|\MyParcelNL\Pdk\Carrier\Model\Carrier|array|null $value
     *
     * @return $this
     */
    protected function setCarrierAttribute($value): self
    {
        if ($value instanceof Carrier) {
            $this->attributes['carrier'] = $value->carrier;
            return $this;
        }

        if (null === $value || is_string($value)) {
            $this->attributes['carrier'] = $value;
            return $this;
        }

        if (is_array($value)) {
            // Array form of a Carrier model (e.g. from toArray() via changeArrayKeysCase).
            // Extract the carrier name string if present.
            $name                        = $value['carrier'] ?? null;
            $this->attributes['carrier'] = is_string($name) ? $name : null;
            return $this;
        }

        // @phpstan-ignore deadCode.unreachable
        throw new InvalidArgumentException(
            sprintf(
                'Carrier must be a string name or a %s instance, %s given.',
                Carrier::class,
                is_object($value) ? get_class($value) : gettype($value)
            )
        );
    }
}
