<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Language\Repository\LanguageRepository;
use MyParcelNL\Pdk\Language\Service\AbstractLanguageService;

class MockAbstractLanguageService extends AbstractLanguageService
{
    private string $language = 'en-GB';

    public function __construct(LanguageRepository $repository, FileSystemInterface $fileSystem)
    {
        parent::__construct($repository, $fileSystem);

        $dir                = $this->fileSystem->dirname($this->getFilePath());
        $translationsPathNl = $this->getFilePath('nl');
        $translationsPathEn = $this->getFilePath('en');

        $this->fileSystem->mkdir($dir, true);

        $this->fileSystem->put(
            $translationsPathNl,
            json_encode([
                'send_help'               => 'Stuur hulp',
                'i_am_trapped'            => 'Ik zit vast',
                'in_a_docker_environment' => 'In een Docker-omgeving',
            ])
        );

        $this->fileSystem->put(
            $translationsPathEn,
            json_encode([
                'send_help'               => 'Send help',
                'i_am_trapped'            => 'I am stuck',
                'in_a_docker_environment' => 'In a Docker environment',
            ])
        );
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return $this
     * @noinspection PhpUnused
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @param  null|string $language
     */
    protected function getFilePath(?string $language = null): string
    {
        return sprintf('%s/../../config/.tmp-translations%s', __DIR__, $language ? "-$language" : '');
    }
}
