<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Handler;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
use MyParcelNL\Pdk\App\Endpoint\Contract\VersionedResourceInterface;
use MyParcelNL\Pdk\App\Endpoint\Request\GetDeliveryOptionsV1Request;
use MyParcelNL\Pdk\App\Endpoint\Resource\DeliveryOptionsV1Resource;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Endpoint handler for retrieving order delivery options.
 *
 * Fetches and formats delivery options from order data using version-specific handlers.
 */
class GetDeliveryOptionsEndpoint extends AbstractEndpoint
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct()
    {
        $this->orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    }

    /**
     * Handle the delivery options request.
     */
    public function handle(Request $request): Response
    {
        $version = $this->detectVersion($request);
        $versionedRequest = $this->createVersionedRequest($request, $version);

        if (! $versionedRequest->validate()) {
            return $versionedRequest->createValidationErrorResponse();
        }

        $orderId = $versionedRequest->getOrderId();

        Logger::debug('Fetching delivery options for order', ['orderId' => $orderId]);

        try {
            $order = $this->orderRepository->get($orderId);
            $deliveryOptions = $order->getDeliveryOptions();

            $resource = $this->createVersionedResourceResponse([
                'orderId' => $orderId,
                'deliveryOptions' => $deliveryOptions,
            ], $version);

            return $resource::createResponse([
                'orderId' => $orderId,
                'deliveryOptions' => $deliveryOptions,
            ], $request);
        } catch (\Exception $exception) {
            Logger::error('Failed to fetch delivery options', [
                'orderId' => $orderId,
                'error' => $exception->getMessage(),
            ]);

            $resource = $this->createVersionedResourceResponse([
                'type'     => 'https://errors.myparcel/delivery-options-error',
                'title'    => 'Delivery Options Error',
                'status'   => 500,
                'detail'   => 'Failed to retrieve delivery options for the specified order',
                'instance' => $request->getPathInfo(),
            ], $version);

            return $resource::createResponse([
                'type'     => 'https://errors.myparcel/delivery-options-error',
                'title'    => 'Delivery Options Error',
                'status'   => 500,
                'detail'   => 'Failed to retrieve delivery options for the specified order',
                'instance' => $request->getPathInfo(),
            ], $request, 500);
        }
    }

    /**
     * Validate the request has required parameters.
     */
    public function validate(Request $request): bool
    {
        $version = $this->detectVersion($request);
        $versionedRequest = $this->createVersionedRequest($request, $version);
        return $versionedRequest->validate();
    }

    /**
     * Create version-specific request based on detected version.
     */
    protected function createVersionedRequest(Request $request, int $version): GetDeliveryOptionsV1Request
    {
        switch ($version) {
            case 1:
                return new GetDeliveryOptionsV1Request($request);
            // case 2: return new GetDeliveryOptionsV2Request($request); // Future versions
            default:
                return new GetDeliveryOptionsV1Request($request); // Default fallback to v1
        }
    }

    /**
     * Create version-specific resource response based on detected version.
     */
    protected function createVersionedResourceResponse(array $data, int $version): VersionedResourceInterface
    {
        switch ($version) {
            case 1:
                $resourceClass = DeliveryOptionsV1Resource::class;
                break;
            // case 2: $resourceClass = DeliveryOptionsV2Resource::class; break; // Future versions
            default:
                $resourceClass = DeliveryOptionsV1Resource::class; // Default fallback to v1
                break;
        }

        return new $resourceClass();
    }
}
