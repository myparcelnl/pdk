<?php

namespace MyParcelNL\Pdk\Proposition;

use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;

interface PropositionManagerInterface
{
    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * @return string
     */
    public function getPropositionName(): string;

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function getCarriers(): CarrierCollection;
}
