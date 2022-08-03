<?php
/** @noinspection PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Form\Helpers\FormBuilder;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);
    $this->formBuilder = new Helpers\FormBuilder();
});

it('get customs settings view form', function () {

    $form = $this->formBuilder->getCustomsView();
    return $form;
});
