<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Language\Repository;

use MyParcelNL\Pdk\Base\Repository\AbstractRepository;

class LanguageRepository extends AbstractRepository
{
    /**
     * @param  string   $language
     * @param  callable $callback
     *
     * @return array<string, string>
     */
    public function getTranslations(string $language, callable $callback): array
    {
        return $this->retrieve("translations_$language", $callback);
    }
}
