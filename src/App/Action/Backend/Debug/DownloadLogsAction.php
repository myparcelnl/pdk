<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Debug;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadLogsAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface
     */
    private $zipService;

    /**
     * @param  \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService
     */
    public function __construct(ZipServiceInterface $zipService)
    {
        $this->zipService = $zipService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $path = $this->createZipPath();

        $this->createLogsZip($path);

        $response = new BinaryFileResponse($path);

        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, basename($path));

        $response->headers->set('Content-Disposition', $disposition);
        $response->deleteFileAfterSend();

        return $response;
    }

    /**
     * @param  string $path
     *
     * @return void
     */
    protected function createLogsZip(string $path): void
    {
        $logFiles = Logger::getLogFiles();

        $this->zipService->create($path);

        foreach ($logFiles as $filePath) {
            $this->zipService->addFile($filePath);
        }

        $this->zipService->close();
    }

    /**
     * @return string
     */
    protected function createZipPath(): string
    {
        $appInfo = Pdk::getAppInfo();

        $timestamp = date('Y-m-d_H-i-s');
        $filename  = "{$timestamp}_{$appInfo->name}_logs.zip";

        return $appInfo->createPath($filename);
    }
}
