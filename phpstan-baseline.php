<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Variable \\$carrierSettings on left side of \\?\\? always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/DeliveryOptions/Service/ShipmentOptionsService.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$productSettings on left side of \\?\\? always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/App/DeliveryOptions/Service/ShipmentOptionsService.php',
];
$ignoreErrors[] = [
	'message' => '#^Unsafe usage of new static\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/Factory/PdkFactory.php',
];
$ignoreErrors[] = [
	'message' => '#^Unsafe usage of new static\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Base/PdkBootstrapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method MyParcelNL\\\\Pdk\\\\Frontend\\\\Form\\\\Builder\\\\AbstractFormBuilderCore\\:\\:__isset\\(\\) should return bool but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Frontend/Form/Builder/AbstractFormBuilderCore.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$value in isset\\(\\) always exists and is always null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Frontend/Form/Builder/FormOperationBuilder.php',
];
$ignoreErrors[] = [
	'message' => '#^Unsafe usage of new static\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Fulfilment/Model/Order.php',
];
$ignoreErrors[] = [
	'message' => '#^Unsafe usage of new static\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Shipment/Model/CustomsDeclarationItem.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
