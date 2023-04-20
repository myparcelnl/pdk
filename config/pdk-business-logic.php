<?php

declare(strict_types=1);

use function DI\value;

/**
 * Business logic for the MyParcel API, for things we cannot retrieve from the API.
 */
return [
    'customsCodeMaxLength'  => value(10),
    'dropOffDelayMaximum'   => value(14),
    'dropOffDelayMinimum'   => value(0),
    'numberSuffixMaxLength' => value(6),
];
