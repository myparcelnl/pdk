<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use MyParcelNL\Pdk\Base\Concern\HasUserAgent;

const PACKAGE_NAME = 'myparcelnl/pdk';

uses(HasUserAgent::class);

it('set user agent', function ($platform, $version, $output) {
    $this->setUserAgent($platform, $version);

    expect($this->getUserAgentHeader())
        ->toBe($output);
})->with([
    'one attribute' => [
        'platform' => 'Bloemkool',
        'version'  => '5.0.0',
        'output'   => sprintf(
            'Bloemkool/5.0.0 MyParcelNL-PDK/%s php/7.4.30',
            InstalledVersions::getPrettyVersion(PACKAGE_NAME)
        ),
    ],
]);

it('sets multiple user agents', function ($input, $output) {
    $this->setUserAgents($input);

    expect($this->getUserAgentHeader())
        ->toBe($output);
})->with([
    'multiple attributes' => [
        'input' => [
            [
                'platform' => 'Bloemkool',
                'version'  => '2.5.0',
            ],
            [
                'platform' => 'Broccoli',
                'version'  => '4.1.2',
            ],
        ],
        'output' => sprintf(
            'Bloemkool/2.5.0 Broccoli/4.1.2 MyParcelNL-PDK/%s php/7.4.30',
            InstalledVersions::getPrettyVersion(PACKAGE_NAME)
        ),
    ],
]);

it('reset the useragent', function ($output) {
    $this->resetUserAgent();

    expect($this->getUserAgentHeader())
        ->toBe($output);
})->with([
    'output' => sprintf('MyParcelNL-PDK/%s php/7.4.30', InstalledVersions::getPrettyVersion(PACKAGE_NAME)),
]);
