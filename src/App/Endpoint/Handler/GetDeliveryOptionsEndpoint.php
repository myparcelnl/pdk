<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Handler;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedRequest;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;
use MyParcelNL\Pdk\App\Endpoint\Contract\VersionedResourceInterface;
use MyParcelNL\Pdk\App\Endpoint\Request\GetDeliveryOptionsV1Request;
use MyParcelNL\Pdk\App\Endpoint\Resource\DeliveryOptionsV1Resource;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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
     * Get all API versions supported by this endpoint.
     *
     * @return int[]
     */
    public function getSupportedVersions(): array
    {
        return [1];
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
            $order = $this->orderRepository->find($orderId);
            // Return problem details not found error if order is missing
            if (!$order) {
                return $versionedRequest->createNotFoundErrorResponse(\sprintf('Order not found for the orderId %s', $orderId));
            }

            // Resolve the shipment options before passing them to the resource
            /** @var PdkOrderOptionsServiceInterface $orderOptionsService */
            $orderOptionsService = Pdk::get(PdkOrderOptionsServiceInterface::class);
            $orderOptionsService->calculateShipmentOptions($order);

            return $this->createVersionedResource($order->deliveryOptions, $version)
                ->createResponse($request, 200, $this->getSupportedVersions());
        } catch (\Exception $exception) {
            Logger::error('Failed to fetch delivery options', [
                'orderId' => $orderId,
                'error' => $exception->getMessage(),
            ]);
            return $versionedRequest->createInternalServerErrorResponse('Internal server error');
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
     *
     * @return GetDeliveryOptionsV1Request|AbstractVersionedRequest
     */
    public function createVersionedRequest(Request $request, int $version): AbstractVersionedRequest
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
    public function createVersionedResource(Arrayable $deliveryOptions, int $version): AbstractVersionedResource
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

        return new $resourceClass($deliveryOptions);
    }
}
