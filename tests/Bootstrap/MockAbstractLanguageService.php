<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Language\Repository\LanguageRepository;
use MyParcelNL\Pdk\Language\Service\AbstractLanguageService;

/**
 * Use this service when you need to test actual translation logic. It does not ignore missing translations and will
 * throw an error when using a language that is not set.
 *
 * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockLanguageService
 */
class MockAbstractLanguageService extends AbstractLanguageService
{
    /**
     * @var string
     */
    private $language = 'en-GB';

    /**
     * @param  \MyParcelNL\Pdk\Language\Repository\LanguageRepository $repository
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface               $fileSystem
     */
    public function __construct(LanguageRepository $repository, FileSystemInterface $fileSystem)
    {
        parent::__construct($repository, $fileSystem);

        $this->reset();
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Set translations for all languages we support by default.
     *
     * @return void
     * @see 'availableLanguages' in the config
     */
    public function reset(): void
    {
        $dir = $this->fileSystem->dirname($this->getFilePath());
        $this->fileSystem->mkdir($dir, true);

        $this->setTranslations('nl', [
            'send_help'               => 'Stuur hulp',
            'i_am_trapped'            => 'Ik zit vast',
            'in_a_docker_environment' => 'In een Docker-omgeving',
        ]);

        $this->setTranslations('en', [
            'send_help'               => 'Send help',
            'i_am_trapped'            => 'I am stuck',
            'in_a_docker_environment' => 'In a Docker environment',
        ]);

        $this->setTranslations('fr', [
            'send_help'               => 'Envoyer de l\'aide',
            'i_am_trapped'            => 'Je suis coincé',
            'in_a_docker_environment' => 'Dans un environnement Docker',
        ]);
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
     * @param  string $language
     * @param  array  $translations
     *
     * @return void
     */
    public function setTranslations(string $language, array $translations): void
    {
        $translationsPath = $this->getFilePath($language);

        $this->fileSystem->put($translationsPath, json_encode($translations));

        // Clear repository cache for the given language
        $this->repository->save("language_$language", null);
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
