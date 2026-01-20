<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Base;

use Brick\VarExporter\VarExporter;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\Support\Str;

final class BehatConfig extends Config
{
    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystem $fileSystem
     */
    public function __construct(FileSystem $fileSystem)
    {
        parent::__construct($fileSystem);
    }

    /**
     * @param  string                                               $httpMethod
     * @param  string                                               $uri
     * @param  array                                                $options
     * @param  \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface $response
     *
     * @return void
     * @throws \Brick\VarExporter\ExportException
     */
    public function writeExample(
        string                  $httpMethod,
        string                  $uri,
        array                   $options,
        ClientResponseInterface $response
    ): void {
        $filename = $this->generateExampleFilename($httpMethod, $uri);

        $body = $response->getBody();

        $bodyString = 'null';

        if ($body) {
            $bodyString = sprintf('json_encode(%s)', VarExporter::export(json_decode($body, true)));
        }

        $this->fileSystem->put(
            $filename,
            strtr(
                $this->getExampleTemplate(),
                [
                    ':BODY'        => $bodyString,
                    ':CONDITION'   => $this->buildCondition($httpMethod, $uri, $options),
                    ':STATUS_CODE' => $response->getStatusCode(),
                ]
            )
        );
    }

    /**
     * @return string
     */
    protected function getExampleTemplate(): string
    {
        return <<<'EOF'
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return :CONDITION;
    },

    'response' => static function (Request $request): ClientResponseInterface {
        return new BehatClientResponse(:BODY, :STATUS_CODE);
    },
];

EOF;
    }

    /**
     * @param  string $uri
     *
     * @return string
     */
    protected function getPath(string $uri): string
    {
        return parse_url($uri, PHP_URL_PATH) ?: '';
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return string
     */
    private function buildCondition(string $httpMethod, string $uri, array $options): string
    {
        $conditions = [
            sprintf('\'%s\' === $request->getPathInfo()', $this->getPath($uri)),
            sprintf('\'%s\' === $request->getMethod()', $httpMethod),
        ];

        return implode("\n            && ", $conditions);
    }

    /**
     * @param  string   $httpMethod
     * @param  string   $uri
     * @param  null|int $increment
     *
     * @return string
     */
    private function generateExampleFilename(string $httpMethod, string $uri, ?int $increment = null): string
    {
        $filename = sprintf(
            '%s/%s%s%s.inc',
            Pdk::get('behatExamplesDir'),
            $httpMethod,
            Str::snake(str_replace('/', '_', $this->getPath($uri))),
            $increment ? "_$increment" : ''
        );

        if ($this->fileSystem->fileExists($filename)) {
            return $this->generateExampleFilename($httpMethod, $uri, $increment + 1);
        }

        return $filename;
    }
}
