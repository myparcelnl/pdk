<?php

declare(strict_types=1);

use function DI\value;

return [
    /**
     * The minimum PHP version required to run the app.
     */
    'minimumPhpVersion'         => value('7.1'),

    /**
     * The version of the delivery options in the checkout.
     *
     * @see https://github.com/myparcelnl/delivery-options/releases
     */
    'deliveryOptionsVersion'    => value('5.7.1'),

    /**
     * The version of vue in the delivery options.
     */
    'deliveryOptionsVueVersion' => value('2.6.13'),

    /**
     * The version of vue in the PDK admin.
     */
    'vueVersion'                => value('3.3.4'),

    /**
     * The version of vue demi in the PDK admin.
     */
    'vueDemiVersion'            => value('0.14.5'),
];
