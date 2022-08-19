<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Language\Service;

use MyParcelNL\Pdk\Language\Repository\LanguageRepository;

abstract class AbstractLanguageService implements LanguageServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Language\Repository\LanguageRepository
     */
    protected $repository;

    /**
     * @param  \MyParcelNL\Pdk\Language\Repository\LanguageRepository $repository
     */
    public function __construct(LanguageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  null|string $language
     *
     * @return string
     */
    abstract protected function getFilePath(?string $language = null): string;

    /**
     * @return array<string, string>
     */
    public function getTranslations(?string $language = null): array
    {
        $lang = $language ?? $this->getLanguage();

        return $this->repository->getTranslations($lang, function () use ($lang) {
            return json_decode(file_get_contents($this->getFilePath($lang)), true);
        });
    }

    /**
     * @param  string      $key
     * @param  null|string $language
     *
     * @return string
     */
    public function translate(string $key, ?string $language = null): string
    {
        $translations = $this->getTranslations($language);

        if (! array_key_exists($key, $translations)) {
            return $key;
        }

        return $translations[$key];
    }
}
