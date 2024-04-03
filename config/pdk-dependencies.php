<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use function DI\factory;
use function DI\value;

return [
    /**
     * The current version of the pdk according to the composer.json file.
     */
    'pdkVersion'                => factory(function (FileSystemInterface $fileSystem): string {
        $rootDir      = Pdk::get('rootDir');
        $composerJson = json_decode($fileSystem->get("$rootDir/composer.json"), true);

        return $composerJson['version'];
    }),

    /**
     * The next major version of the pdk. Used for deprecation messages.
     */
    'pdkNextMajorVersion'       => factory(function (): string {
        $version = Pdk::get('pdkVersion');

        return (int) explode('.', $version)[0] + 1 . '.0.0';
    }),

    /**
     * The minimum PHP version required to run the app.
     */
    'minimumPhpVersion'         => value('7.1'),

    /**
     * The version of the delivery options in the checkout.
     *
     * @see https://github.com/myparcelnl/delivery-options/releases
     */
    'deliveryOptionsVersion'    => value('6'),

    /**
     * The version of vue in the delivery options.
     */
    'deliveryOptionsVueVersion' => factory(function (): string {
        return Pdk::get('vueVersion');
    }),

    'deliveryOptionsCdnUrlJs' => factory(function (): string {
        return strtr(Pdk::get('baseCdnUrl'), [
            ':name'     => '@myparcel/delivery-options',
            ':version'  => Pdk::get('deliveryOptionsVersion'),
            ':filename' => 'myparcel.lib.js',
        ]);
    }),

    'deliveryOptionsCdnUrlCss' => factory(function (): string {
        return strtr(Pdk::get('baseCdnUrl'), [
            ':name'     => '@myparcel/delivery-options',
            ':version'  => Pdk::get('deliveryOptionsVersion'),
            ':filename' => 'style.css',
        ]);
    }),

    /**
     * The version of vue in the PDK admin.
     */
    'vueVersion'               => value('3.4'),

    /**
     * The version of vue-demi in the PDK admin.
     */
    'vueDemiVersion'           => value('0.14'),

    /**
     * Whether the current php version is supported.
     */
    'isPhpVersionSupported'    => factory(function (): bool {
        return version_compare(PHP_VERSION, Pdk::get('minimumPhpVersion'), '>=');
    }),
];
