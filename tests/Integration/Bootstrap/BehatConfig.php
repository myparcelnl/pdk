<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Bootstrap;

use Brick\VarExporter\VarExporter;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Sdk\src\Support\Str;

final class BehatConfig extends Config
{
    private const ADDITIONAL_CONFIG_DIR = __DIR__ . '/..';
    private const EXAMPLES_DIR          = self::ADDITIONAL_CONFIG_DIR . '/Examples';

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

        file_put_contents(
            $filename,
            strtr(
                $this->getExampleTemplate(),
                [
                    ':METHOD'      => $httpMethod,
                    ':PATH'        => $this->getPath($uri),
                    ':BODY'        => $bodyString,
                    ':STATUS_CODE' => $response->getStatusCode(),
                ]
            )
        );
    }

    /**
     * @return array|string[]
     */
    protected function getConfigDirs(): array
    {
        return array_merge([
            self::ADDITIONAL_CONFIG_DIR,
        ], parent::getConfigDirs());
    }

    /**
     * @return string
     */
    protected function getExampleTemplate(): string
    {
        return <<<'EOF'
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Response\ClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return ':PATH' === $request->getPathInfo() 
          && ':METHOD' === $request->getMethod();
    },

    'response' => static function (Request $request): ClientResponse {
        return new ClientResponse(:BODY, :STATUS_CODE);
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
     *
     * @return string
     */
    private function generateExampleFilename(string $httpMethod, string $uri): string
    {
        return sprintf(
            '%s/%s%s.inc',
            self::EXAMPLES_DIR,
            $httpMethod,
            Str::snake(str_replace('/', '_', $this->getPath($uri)))
        );
    }
}
