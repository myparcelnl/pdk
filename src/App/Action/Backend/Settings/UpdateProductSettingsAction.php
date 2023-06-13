<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductSettingsAction extends AbstractOrderAction
{
    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface */
    private $productRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository
     */
    public function __construct(PdkProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function handle(Request $request): Response
    {
        $productSettings = $request->query->get('productSettings');
        $productId       = $request->query->get('productId');

        if (empty($productSettings)) {
            throw new InvalidArgumentException('Request body is empty');
        }

        $product = new PdkProduct([
            'externalIdentifier' => $productId,
            'settings'           => $productSettings,
        ]);

        $this->productRepository->update($product);

        return new JsonResponse([
            'product_settings' => $product->settings->toArray(),
        ]);
    }
}

