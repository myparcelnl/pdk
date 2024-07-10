<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Settings;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductSettingsAction implements ActionInterface
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
     */
    public function handle(Request $request): Response
    {
        $productId = $request->query->get('productId');

        $body     = json_decode($request->getContent(), true);
        $settings = $body['data']['product_settings'] ?? [];

        $product = $this->productRepository->getProduct($productId);

        $product->settings = $settings;

        $this->productRepository->update($product);

        return new JsonResponse([
            'product_settings' => $product->settings->toArrayWithoutNull(),
        ]);
    }
}

