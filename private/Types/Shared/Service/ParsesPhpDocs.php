<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Service;

use MyParcelNL\Pdk\Console\Types\Shared\Concern\UsesCache;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

trait ParsesPhpDocs
{
    use UsesCache;

    /**
     * @var \Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor
     */
    private $phpDocExtractor;

    /**
     * @return \Symfony\Component\PropertyInfo\Type[]
     */
    protected function getPhpDocTypes(string $refName, string $propertyName): array
    {
        $this->phpDocExtractor ??= new PhpDocExtractor();

        return $this->phpDocExtractor->getTypes($refName, $propertyName) ?? [];
    }

    /**
     * @param  \ReflectionClass|\ReflectionMethod $ref
     *
     * @return string
     */
    protected function getPlainTextFromDocComment($ref): ?string
    {
        $docComment = $ref->getDocComment();

        if (! $docComment) {
            return null;
        }

        $trimmed = substr($docComment, 3, -2);

        $withoutTags = preg_replace('/\s+@.*$/m', '', $trimmed);
        $plainText   = preg_replace('/\s+\*/m', '', $withoutTags);

        $finalText = trim($plainText);

        return $finalText ?: null;
    }

    /**
     * @return \Symfony\Component\PropertyInfo\Type[]
     */
    protected function getReflectionTypes(string $refName, string $propertyName): array
    {
        $this->reflectionExtractor ??= new ReflectionExtractor();

        return $this->reflectionExtractor->getTypes($refName, $propertyName) ?? [];
    }
}
