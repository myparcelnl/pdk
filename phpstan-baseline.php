<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method MyParcelNL\\\\Pdk\\\\App\\\\Order\\\\Contract\\\\PdkOrderRepositoryInterface\\:\\:save\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Action/Backend/Order/ExportOrderAction.php',
];
$ignoreErrors[] = [
	'message' => '#^Property MyParcelNL\\\\Pdk\\\\App\\\\Order\\\\Model\\\\PdkOrder\\:\\:\\$shipments \\(MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection\\|null\\) does not accept array\\<int, mixed\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Action/Backend/Order/ExportOrderAction.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\App\\\\Action\\\\Backend\\\\Webhook\\\\CreateWebhooksAction\\:\\:getWebhookUrl\\(\\) never returns null so it can be removed from the return type\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Action/Backend/Webhook/CreateWebhooksAction.php',
];
$ignoreErrors[] = [
	'message' => '#^Ternary operator condition is always true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Action/Backend/Webhook/CreateWebhooksAction.php',
];
$ignoreErrors[] = [
	'message' => '#^Ternary operator condition is always true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Action/Shared/Context/FetchContextAction.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\App\\\\Order\\\\Collection\\\\PdkOrderCollection\\:\\:mergeShipmentsByOrder\\(\\) should return MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection but returns MyParcelNL\\\\Pdk\\\\Base\\\\Support\\\\Collection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Order/Collection/PdkOrderCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type MyParcelNL\\\\Pdk\\\\Base\\\\Support\\\\Collection is not subtype of native type MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Order/Collection/PdkOrderCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection\\|null is not subtype of native type MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Order/Collection/PdkOrderCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type mixed is not subtype of native type MyParcelNL\\\\Pdk\\\\App\\\\Order\\\\Collection\\\\PdkOrderNoteCollection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/Order/Contract/PdkOrderNoteRepositoryInterface.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @extends has invalid value \\(\\\\MyParcelNL\\\\Pdk\\\\Base\\\\Model\\\\Model\\)\\: Unexpected token "\\\\n ", expected \'\\<\' at offset 48$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Concern/HasPrices.php',
];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Facade.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property MyParcelNL\\\\Pdk\\\\Base\\\\Factory\\\\PdkFactory\\:\\:\\$mode \\(string\\) on left side of \\?\\? is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Factory/PdkFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\Base\\\\Contract\\\\Arrayable\\:\\:toArray\\(\\) invoked with 1 parameter, 0 required\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Model/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'date\' on array\\{date\\: string, timezone\\: string, timezone_type\\: int\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Model/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Model/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property MyParcelNL\\\\Pdk\\\\Base\\\\PdkBootstrapper\\:\\:\\$pdk \\(MyParcelNL\\\\Pdk\\\\Base\\\\Pdk\\) does not accept MyParcelNL\\\\Pdk\\\\Base\\\\Concern\\\\PdkInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/PdkBootstrapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Property MyParcelNL\\\\Pdk\\\\Base\\\\Repository\\\\Repository\\:\\:\\$storageHashMap \\(array\\{string, string\\}\\) does not accept default value of type array\\{\\}\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Repository/Repository.php',
];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always true\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Base/Service/CurrencyService.php',
];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Support/Arr.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\Base\\\\Contract\\\\Arrayable\\:\\:toArray\\(\\) invoked with 1 parameter, 0 required\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Support/Collection.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Context/Model/CheckoutContext.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\Context\\\\Service\\\\ContextService\\:\\:createContextCollection\\(\\) should return MyParcelNL\\\\Pdk\\\\Context\\\\Collection\\\\ProductDataContextCollection but returns MyParcelNL\\\\Pdk\\\\Base\\\\Support\\\\Collection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Context/Service/ContextService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\Context\\\\Service\\\\ContextService\\:\\:createOrderDataContext\\(\\) should return MyParcelNL\\\\Pdk\\\\Context\\\\Collection\\\\OrderDataContextCollection but returns MyParcelNL\\\\Pdk\\\\Context\\\\Collection\\\\ProductDataContextCollection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Context/Service/ContextService.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Collection\\\\OrderCollection\\:\\:addIds\\(\\) should return \\$this\\(MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Collection\\\\OrderCollection\\) but returns static\\(MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Collection\\\\OrderCollection\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Fulfilment/Collection/OrderCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Model\\\\Product\\:\\:fromPdkProduct\\(\\) should return static\\(MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Model\\\\Product\\) but returns MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Model\\\\Product\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Fulfilment/Model/Product.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type string\\|null is not subtype of native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Fulfilment/Request/PostOrderNotesRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Request\\\\PostOrderNotesRequest\\:\\:\\$collection \\(MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Collection\\\\OrderCollection\\) does not accept MyParcelNL\\\\Pdk\\\\Fulfilment\\\\Collection\\\\OrderNoteCollection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Fulfilment/Request/PostOrderNotesRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type MyParcelNL\\\\Pdk\\\\Settings\\\\Model\\\\Settings\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Settings/Repository/AbstractSettingsRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$existing in PHPDoc tag @var does not match assigned variable \\$id\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Settings/Repository/AbstractSettingsRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Collection/ShipmentCollection.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method MyParcelNL\\\\Pdk\\\\Api\\\\Contract\\\\ApiServiceInterface\\:\\:getBaseUrl\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Repository/ShipmentRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection is incompatible with native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Repository/ShipmentRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Property MyParcelNL\\\\Pdk\\\\Shipment\\\\Request\\\\FetchShipmentsRequest\\:\\:\\$ids \\(MyParcelNL\\\\Pdk\\\\Base\\\\Support\\\\Collection\\) does not accept MyParcelNL\\\\Sdk\\\\src\\\\Support\\\\Collection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Request/FetchShipmentsRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property MyParcelNL\\\\Pdk\\\\Shipment\\\\Request\\\\FetchShipmentsRequest\\:\\:\\$referenceIdentifiers \\(MyParcelNL\\\\Pdk\\\\Base\\\\Support\\\\Collection\\) does not accept MyParcelNL\\\\Sdk\\\\src\\\\Support\\\\Collection\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Request/FetchShipmentsRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'positions\' on array\\{string, string\\} on left side of \\?\\? does not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Request/GetLabelsRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type string\\|null is not subtype of native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Request/PostShipmentsRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$groupedShipments of method MyParcelNL\\\\Pdk\\\\Shipment\\\\Request\\\\PostShipmentsRequest\\:\\:encodeSecondaryShipments\\(\\) expects MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection, MyParcelNL\\\\Pdk\\\\Base\\\\Support\\\\Collection given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Request/PostShipmentsRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection is incompatible with native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Response/GetLabelsPdfResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection is incompatible with native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Response/GetLabelsResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method DateTimeInterface\\:\\:modify\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Service/DropOffService.php',
];
$ignoreErrors[] = [
	'message' => '#^Ternary operator condition is always true\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Shipment/Service/DropOffService.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type bool\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Webhook/Repository/WebhookSubscriptionRepository.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
