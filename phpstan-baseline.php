<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	// identifier: isset.offset
	'message' => '#^Offset \'date\' on array\\{date\\: string, timezone\\: string, timezone_type\\: int\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Model/Model.php',
];
$ignoreErrors[] = [
	// identifier: return.phpDocType
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Model/Model.php',
];
$ignoreErrors[] = [
	// identifier: assign.propertyType
	'message' => '#^Static property MyParcelNL\\\\Pdk\\\\Base\\\\PdkBootstrapper\\:\\:\\$pdk \\(MyParcelNL\\\\Pdk\\\\Base\\\\Pdk\\) does not accept MyParcelNL\\\\Pdk\\\\Base\\\\Concern\\\\PdkInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/PdkBootstrapper.php',
];
$ignoreErrors[] = [
	// identifier: booleanNot.alwaysTrue
	'message' => '#^Negated boolean expression is always true\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Base/Service/CurrencyService.php',
];
$ignoreErrors[] = [
	// identifier: return.phpDocType
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type MyParcelNL\\\\Pdk\\\\Settings\\\\Model\\\\Settings\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Settings/Repository/AbstractPdkSettingsRepository.php',
];
$ignoreErrors[] = [
	// identifier: varTag.differentVariable
	'message' => '#^Variable \\$existing in PHPDoc tag @var does not match assigned variable \\$id\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Settings/Repository/AbstractPdkSettingsRepository.php',
];
$ignoreErrors[] = [
	// identifier: return.phpDocType
	'message' => '#^PHPDoc tag @return with type void is incompatible with native type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Collection/ShipmentCollection.php',
];
$ignoreErrors[] = [
	// identifier: method.notFound
	'message' => '#^Call to an undefined method MyParcelNL\\\\Pdk\\\\Api\\\\Contract\\\\ApiServiceInterface\\:\\:getBaseUrl\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Repository/ShipmentRepository.php',
];
$ignoreErrors[] = [
	// identifier: return.phpDocType
	'message' => '#^PHPDoc tag @return with type MyParcelNL\\\\Pdk\\\\Shipment\\\\Collection\\\\ShipmentCollection is incompatible with native type string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Repository/ShipmentRepository.php',
];
$ignoreErrors[] = [
	// identifier: nullCoalesce.offset
	'message' => '#^Offset \'positions\' on array\\{string, string\\} on left side of \\?\\? does not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Request/GetLabelsRequest.php',
];
$ignoreErrors[] = [
	// identifier: method.notFound
	'message' => '#^Call to an undefined method DateTimeInterface\\:\\:modify\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Service/DropOffService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
