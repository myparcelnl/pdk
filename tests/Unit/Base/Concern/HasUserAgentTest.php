<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Concern\HasUserAgent;

uses(HasUserAgent::class);

it('set user agent', function ($platform, $version, $output) {
    $this->setUserAgent($platform, $version);

    expect($this->getUserAgentHeader())
        ->toBe($output);
})->with([
    'one attribute' => [
        'platform' => 'Bloemkool',
        'version'  => '5.0.0',
        'output'   => 'Bloemkool/5.0.0 MyParcelNL-PDK/1.13.0 php/7.4.30',
    ],
]);

it('sets multiple user agents', function ($input, $output) {
    $this->setUserAgents($input);

    expect($this->getUserAgentHeader())
        ->toBe($output);
})->with([
    'multiple attributes' => [
        'input'  => [
            [
                'platform' => 'Bloemkool',
                'version'  => '2.5.0',
            ],
            [
                'platform' => 'Broccoli',
                'version'  => '4.1.2',
            ],
        ],
        'output' => 'Bloemkool/2.5.0 Broccoli/4.1.2 MyParcelNL-PDK/1.13.0 php/7.4.30',
    ],
]);

it('reset the useragent', function ($output) {
    $this->resetUserAgent();

    expect($this->getUserAgentHeader())
        ->toBe($output);
})->with([
    'output' => 'MyParcelNL-PDK/1.13.0 php/7.4.30',
]);
