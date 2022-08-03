<?php
/** @noinspection PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Form\Model\Input\Select\DropOffDaySelect;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsView;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);
});

it('creates a select field', function() {
    $data = [
        'label'          => 'Show delivery date',
        'name'           => 'featureShowDeliveryDate',
        'desc'           => 'This will show the delivery date in the delivery options',
        'options' => [
          [
              'query' => [],
              'id'    => 'id',
              'name'  => 'human',
          ],
        ],
    ];
    $input = CarrierSettingsView::createInput($data);

    expect($input)
        ->toBeInstanceOf(SelectInput::class);
});

it('creates a text field', function() {
    $data = [
        'label' => 'Signature price',
        'name'  => 'priceSignature',
        'desc'  => 'The price for a customer when they activate signature on delivery',
    ];
    $input = CarrierSettingsView::createInput($data);

    expect($input)
        ->toBeInstanceOf(TextInput::class);
});

it('creates a toggle field', function () {
    $data = [
        'isBool' => true,
        'label' => 'Allow delivery on Monday',
        'name' => 'allowMondayDelivery',
        'desc' => 'The package will be delivered on the monday, this is not a default delivery day.',
    ];
    $input = CarrierSettingsView::createInput($data);

    expect($input)
        ->toBeInstanceOf(ToggleInput::class);
});


it('creates a dropoffday selector field', function() {
   $data = [
        'multiple' => true,
        'label' => 'Drop off days',
        'name' => 'dropOffDays',
        'desc' => 'Select all the dropoff days',
        'values' => [
            'query' => [
                ['day_number' => 1, 'name' => 'Monday'],
                ['day_number' => 2, 'name' => 'Tuesday'],
                ['day_number' => 3, 'name' => 'Wednesday'],
                ['day_number' => 4, 'name' => 'Thursday'],
                ['day_number' => 5, 'name' => 'Friday'],
                ['day_number' => 6, 'name' => 'Saturday'],
                ['day_number' => 7, 'name' => 'Sunday'],
            ],
            'id'    => 'day_number',
            'name'  => 'name',
        ],
   ];
   $input = CarrierSettingsView::createInput($data);

   expect($input)
       ->toBeInstanceOf(DropOffDaySelect::class);
});

it('creates all the carriers', function() {

    $allCarriers = [
        CarrierOptions::CARRIER_POSTNL_NAME,
        CarrierOptions::CARRIER_BPOST_NAME,
        CarrierOptions::CARRIER_DPD_ID,
        CarrierOptions::CARRIER_INSTABOX_NAME,
        10932621
    ];

    $collection = new CarrierCollection();

    foreach ($allCarriers as $carrier) {
        $collection->push(new CarrierOptions(['name' => $carrier]));
    }

    expect($collection)
        ->toBeInstanceOf(CarrierCollection::class);
});
