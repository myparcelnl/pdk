<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Language\Repository\LanguageRepository;
use MyParcelNL\Pdk\Language\Service\AbstractLanguageService;
use RuntimeException;

class MockAbstractLanguageService extends AbstractLanguageService
{
    /**
     * @var string
     */
    private $language = 'en-GB';

    /**
     * @param  \MyParcelNL\Pdk\Language\Repository\LanguageRepository $repository
     */
    public function __construct(LanguageRepository $repository)
    {
        $dir                = dirname($this->getFilePath());
        $translationsPathNl = $this->getFilePath('nl');
        $translationsPathEn = $this->getFilePath('en');

        if (! is_dir($dir) && ! mkdir($dir) && ! is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        file_put_contents(
            $translationsPathNl,
            json_encode([
                'send_help'               => 'Stuur hulp',
                'i_am_trapped'            => 'Ik zit vast',
                'in_a_docker_environment' => 'In een Docker-omgeving',
            ])
        );

        file_put_contents(
            $translationsPathEn,
            json_encode([
                'send_help'               => 'Send help',
                'i_am_trapped'            => 'I am stuck',
                'in_a_docker_environment' => 'In a Docker environment',
            ])
        );

        parent::__construct($repository);

        // Delete temporary files after test is done.
        register_shutdown_function(static function () use ($translationsPathEn, $translationsPathNl) {
            if (file_exists($translationsPathNl)) {
                unlink($translationsPathNl);
            }

            if (file_exists($translationsPathEn)) {
                unlink($translationsPathEn);
            }
        });
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param  string $language
     *
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
     *
     * @return string
     */
    protected function getFilePath(?string $language = null): string
    {
        return sprintf('%s/../../config/.tmp-translations%s', __DIR__, $language ? "-$language" : '');
    }
}
