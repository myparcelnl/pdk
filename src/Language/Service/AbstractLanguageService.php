<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Language\Service;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Language\Repository\LanguageRepository;

abstract class AbstractLanguageService implements LanguageServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    protected $fileSystem;

    /**
     * @var \MyParcelNL\Pdk\Language\Repository\LanguageRepository
     */
    protected $repository;

    /**
     * @param  \MyParcelNL\Pdk\Language\Repository\LanguageRepository $repository
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface               $fileSystem
     */
    public function __construct(LanguageRepository $repository, FileSystemInterface $fileSystem)
    {
        $this->repository = $repository;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param  null|string $language
     *
     * @return string
     */
    abstract protected function getFilePath(?string $language = null): string;

    /**
     * @param  null|string $language
     *
     * @return string
     */
    public function getIso2(?string $language = null): string
    {
        $lang = substr($language ?? $this->getLanguage(), 0, 2);

        return in_array($lang, Pdk::get('availableLanguages')) ? $lang : Pdk::get('defaultLanguage');
    }

    /**
     * @return array<string, string>
     */
    public function getTranslations(?string $language = null): array
    {
        $lang = $language ?? $this->getLanguage();
        $iso2 = substr($lang, 0, 2);

        return $this->repository->getTranslations($iso2, function () use ($iso2) {
            return json_decode($this->fileSystem->get($this->getFilePath($iso2)), true);
        });
    }

    /**
     * @param  string      $key
     * @param  null|string $language
     *
     * @return bool
     */
    public function hasTranslation(string $key, ?string $language = null): bool
    {
        return array_key_exists($key, $this->getTranslations($language));
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

    /**
     * @param  array       $array
     * @param  null|string $language
     *
     * @return array
     */
    public function translateArray(array $array, ?string $language = null): array
    {
        if (! Arr::isAssoc($array)) {
            return $this->translateArray(array_combine($array, $array), $language);
        }

        return array_map(function ($value) use ($language) {
            if (null === $value) {
                return null;
            }

            return is_array($value)
                ? $this->translateArray($value, $language)
                : $this->translate($value, $language);
        }, $array);
    }
}
