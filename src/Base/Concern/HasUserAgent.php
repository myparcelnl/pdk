<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use Composer\InstalledVersions;

trait HasUserAgent
{
    /**
     * @var array
     */
    protected $userAgent = [];

    /**
     * @return array
     */
    public function getUserAgent(): array
    {
        return $this->userAgent;
    }

    /**
     * @return array
     */
    public function getUserAgentFromComposer(): array
    {
        $composerData = InstalledVersions::getRootPackage();

        return ['MyParcelNL-PDK' => $composerData['pretty_version']];
    }

    /**
     * @return string
     */
    public function getUserAgentHeader(): string
    {
        $userAgentStrings = [];
        $userAgents       = array_merge(
            $this->getUserAgent(),
            $this->getUserAgentFromComposer(),
            $this->getUserAgentFromPhp()
        );

        foreach ($userAgents as $key => $value) {
            $userAgentStrings[] = $key . '/' . $value;
        }

        return implode(' ', $userAgentStrings);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Concern\HasUserAgent
     */
    public function resetUserAgent(): self
    {
        $this->userAgent = [];

        return $this;
    }

    /**
     * @param  string      $platform
     * @param  null|string $version
     *
     * @return \MyParcelNL\Pdk\Base\Concern\HasUserAgent
     */
    public function setUserAgent(string $platform, ?string $version): self
    {
        $this->userAgent[$platform] = $version;

        return $this;
    }

    /**
     * @param  array $userAgentMap
     *
     * @return \MyParcelNL\Pdk\Base\Concern\HasUserAgent
     */
    public function setUserAgents(array $userAgentMap): self
    {
        foreach ($userAgentMap as $header) {
            $this->setUserAgent($header['platform'], $header['version']);
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getUserAgentFromPhp(): array
    {
        return ['php' => PHP_VERSION];
    }
}
