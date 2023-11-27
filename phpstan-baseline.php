<?php declare(strict_types = 1);

$ignoreErrors = [];
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
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type MyParcelNL\\\\Pdk\\\\Settings\\\\Model\\\\Settings\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Settings/Repository/AbstractPdkSettingsRepository.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$existing in PHPDoc tag @var does not match assigned variable \\$id\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Settings/Repository/AbstractPdkSettingsRepository.php',
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
	'message' => '#^Call to an undefined method DateTimeInterface\\:\\:modify\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Service/DropOffService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
