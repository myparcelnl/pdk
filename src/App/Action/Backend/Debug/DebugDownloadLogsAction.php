<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Debug;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class DebugDownloadLogsAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    protected $fileSystem;

    /**
     * @var \MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface
     */
    protected $logger;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface
     */
    private $zipService;

    /**
     * @param  \MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface $logger
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface           $fileSystem
     * @param  \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface  $zipService
     */
    public function __construct(
        PdkLoggerInterface  $logger,
        FileSystemInterface $fileSystem,
        ZipServiceInterface $zipService
    ) {
        $this->logger     = $logger;
        $this->fileSystem = $fileSystem;
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
        $logs = $this->logger->getLogs();

        $this->zipService->create($path);

        foreach ($logs as $level => $log) {
            if (empty($log)) {
                continue;
            }

            $this->zipService->addFromString("$level.log", $log);
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

        return $appInfo->path . $filename;
    }
}
