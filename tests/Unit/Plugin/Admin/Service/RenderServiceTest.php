<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\RenderService;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

/**
 * Extract the randomly generated id from the html.
 *
 * @param  string $result
 *
 * @return string
 */
function getId(string $result): string
{
    preg_match('/pdk-\d+/m', $result, $matches);
    return (string) $matches[0];
}

it('renders init script', function () {
    $result = RenderService::renderInitScript();

    $expectedHtml = <<<'EOF'
<span id="myparcel-pdk-bootstrap" data-pdk-context="{&quot;global&quot;:{&quot;baseUrl&quot;:&quot;CMS_URL&quot;,&quot;bootstrapId&quot;:&quot;myparcel-pdk-bootstrap&quot;,&quot;endpoints&quot;:{&quot;exportOrder&quot;:{&quot;body&quot;:null,&quot;headers&quot;:[],&quot;method&quot;:&quot;POST&quot;,&quot;path&quot;:&quot;&quot;,&quot;parameters&quot;:{&quot;action&quot;:&quot;exportOrder&quot;}},&quot;exportAndPrintOrder&quot;:{&quot;body&quot;:null,&quot;headers&quot;:[],&quot;method&quot;:&quot;POST&quot;,&quot;path&quot;:&quot;&quot;,&quot;parameters&quot;:{&quot;action&quot;:&quot;exportAndPrintOrder&quot;}},&quot;getOrderData&quot;:{&quot;body&quot;:null,&quot;headers&quot;:[],&quot;method&quot;:&quot;GET&quot;,&quot;path&quot;:&quot;&quot;,&quot;parameters&quot;:{&quot;action&quot;:&quot;getOrderData&quot;}}},&quot;event&quot;:&quot;myparcel_pdk_loaded&quot;,&quot;mode&quot;:&quot;production&quot;,&quot;pluginSettings&quot;:[],&quot;translations&quot;:{&quot;apple_tree&quot;:&quot;Appelboom&quot;}}}"></span>
<script src="https://localhost:9420/src/main.ts" type="module"></script>

EOF;

    expect($result)->toBe(strtr($expectedHtml, ['__ID__' => getId($result)]));
});

it('renders modals', function () {
    $result = RenderService::renderModals();

    $expectedHtml = <<<'EOF'
<div id="__ID__" data-pdk-context="{}"></div>
<script id="__ID__-script">
(() => {
  var e = 'myparcel_pdk_loaded';
  var listener = function(event) {
    event.detail.render('Modals', '__ID__');
    document.getElementById('__ID__-script').remove();
    document.removeEventListener(e, listener);
  };

  document.addEventListener(e, listener);
})();
</script>

EOF;

    expect($result)->toBe(strtr($expectedHtml, ['__ID__' => getId($result)]));
});

it('renders notifications', function () {
    $result = RenderService::renderNotifications();

    $expectedHtml = <<<'EOF'
<div id="__ID__" data-pdk-context="{}"></div>
<script id="__ID__-script">
(() => {
  var e = 'myparcel_pdk_loaded';
  var listener = function(event) {
    event.detail.render('Notifications', '__ID__');
    document.getElementById('__ID__-script').remove();
    document.removeEventListener(e, listener);
  };

  document.addEventListener(e, listener);
})();
</script>

EOF;

    expect($result)->toBe(strtr($expectedHtml, ['__ID__' => getId($result)]));
});

it('renders order card', function () {
    $result = RenderService::renderOrderCard(new PdkOrder(['externalIdentifier' => 'P00924872']));

    $expectedHtml = <<<'EOF'
<div id="__ID__" data-pdk-context="{&quot;orderData&quot;:[{&quot;externalIdentifier&quot;:&quot;P00924872&quot;,&quot;customsDeclaration&quot;:{&quot;contents&quot;:1,&quot;invoice&quot;:null,&quot;items&quot;:[],&quot;weight&quot;:0},&quot;deliveryOptions&quot;:{&quot;carrier&quot;:null,&quot;date&quot;:null,&quot;deliveryType&quot;:&quot;standard&quot;,&quot;labelAmount&quot;:1,&quot;packageType&quot;:&quot;package&quot;,&quot;pickupLocation&quot;:null,&quot;shipmentOptions&quot;:{&quot;ageCheck&quot;:null,&quot;insurance&quot;:null,&quot;labelDescription&quot;:null,&quot;largeFormat&quot;:null,&quot;onlyRecipient&quot;:null,&quot;return&quot;:null,&quot;sameDayDelivery&quot;:null,&quot;signature&quot;:null}},&quot;lines&quot;:[],&quot;recipient&quot;:null,&quot;sender&quot;:null,&quot;shipmentPrice&quot;:null,&quot;shipmentVat&quot;:null,&quot;shipments&quot;:[],&quot;label&quot;:null,&quot;orderPrice&quot;:0,&quot;orderVat&quot;:0,&quot;orderPriceAfterVat&quot;:0,&quot;shipmentPriceAfterVat&quot;:0,&quot;totalPrice&quot;:0,&quot;totalVat&quot;:0,&quot;totalPriceAfterVat&quot;:0}]}"></div>
<script id="__ID__-script">
(() => {
  var e = 'myparcel_pdk_loaded';
  var listener = function(event) {
    event.detail.render('OrderCard', '__ID__');
    document.getElementById('__ID__-script').remove();
    document.removeEventListener(e, listener);
  };

  document.addEventListener(e, listener);
})();
</script>

EOF;

    expect($result)->toBe(strtr($expectedHtml, ['__ID__' => getId($result)]));
});

it('renders order list column', function () {
    $result = RenderService::renderOrderListColumn(new PdkOrder(['externalIdentifier' => 'P00924878']));

    $expectedHtml = <<<'EOF'
<div id="__ID__" data-pdk-context="{&quot;orderData&quot;:[{&quot;externalIdentifier&quot;:&quot;P00924878&quot;,&quot;customsDeclaration&quot;:{&quot;contents&quot;:1,&quot;invoice&quot;:null,&quot;items&quot;:[],&quot;weight&quot;:0},&quot;deliveryOptions&quot;:{&quot;carrier&quot;:null,&quot;date&quot;:null,&quot;deliveryType&quot;:&quot;standard&quot;,&quot;labelAmount&quot;:1,&quot;packageType&quot;:&quot;package&quot;,&quot;pickupLocation&quot;:null,&quot;shipmentOptions&quot;:{&quot;ageCheck&quot;:null,&quot;insurance&quot;:null,&quot;labelDescription&quot;:null,&quot;largeFormat&quot;:null,&quot;onlyRecipient&quot;:null,&quot;return&quot;:null,&quot;sameDayDelivery&quot;:null,&quot;signature&quot;:null}},&quot;lines&quot;:[],&quot;recipient&quot;:null,&quot;sender&quot;:null,&quot;shipmentPrice&quot;:null,&quot;shipmentVat&quot;:null,&quot;shipments&quot;:[],&quot;label&quot;:null,&quot;orderPrice&quot;:0,&quot;orderVat&quot;:0,&quot;orderPriceAfterVat&quot;:0,&quot;shipmentPriceAfterVat&quot;:0,&quot;totalPrice&quot;:0,&quot;totalVat&quot;:0,&quot;totalPriceAfterVat&quot;:0}]}"></div>
<script id="__ID__-script">
(() => {
  var e = 'myparcel_pdk_loaded';
  var listener = function(event) {
    event.detail.render('OrderListColumn', '__ID__');
    document.getElementById('__ID__-script').remove();
    document.removeEventListener(e, listener);
  };

  document.addEventListener(e, listener);
})();
</script>

EOF;

    expect($result)->toBe(strtr($expectedHtml, ['__ID__' => getId($result)]));
});
