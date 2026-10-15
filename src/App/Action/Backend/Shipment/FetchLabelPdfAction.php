<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fetches a labels pdf that the api prepares in the background, for the frontend to poll.
 * Takes only the label id, never a url, so it cannot be used to request anything else.
 */
class FetchLabelPdfAction implements ActionInterface
{
    /** The api error code for a pdf that is not generated yet. */
    private const ERROR_CODE_NOT_READY = 5100;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

    public function __construct(ShipmentRepository $shipmentRepository)
    {
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     */
    public function handle(Request $request): Response
    {
        $labelId = (string) $request->get('labelId', '');

        if (! preg_match('/^[A-Za-z0-9_-]+$/', $labelId)) {
            throw new InvalidArgumentException('Invalid label id');
        }

        try {
            $pdf = $this->shipmentRepository->fetchPreparedLabelPdf($labelId);
        } catch (ApiException $e) {
            if (! $this->isNotReady($e)) {
                throw $e;
            }

            return new JsonResponse(['pdfs' => ['pending' => true]]);
        }

        return new JsonResponse(['pdfs' => ['data' => base64_encode($pdf)]]);
    }

    private function isNotReady(ApiException $exception): bool
    {
        foreach ($exception->getErrors() as $error) {
            if (self::ERROR_CODE_NOT_READY === ($error['code'] ?? null)) {
                return true;
            }
        }

        return false;
    }
}
