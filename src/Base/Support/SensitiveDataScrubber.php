<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

final class SensitiveDataScrubber
{
    const MASK = '***';

    /**
     * Exact lowercase matches for HTTP header names.
     */
    private const SENSITIVE_HEADERS = ['authorization', 'x-api-key', 'api-key'];

    /**
     * Substring patterns (lowercase) matched against query param and body keys.
     * Using stripos() / strpos() after strtolower() catches variants like
     * access_token, refresh_token, api_key, etc.
     */
    private const SENSITIVE_KEY_PATTERNS = [
        'token',
        'jwt',
        'password',
        'secret',
        'credential',
        'api_key',
        'api-key',
        'apikey',
        'address',
        'street',
        'email',
        'phone',
        'zip',
        'postal',
        'postal_code',
        'zip_code',
        'lat',
        'lng',
        'name',
        'last_name',
        'lastName',
        'first_name',
        'firstName',
        'company',
        'customer',
        'client',
        'user',
        'account',
        'bsn'
    ];

    /**
     * Normalise header keys to lowercase and mask sensitive values.
     *
     * Handles both plain-string header values (Guzzle request options) and
     * PSR-7 array-of-strings header values (RequestInterface / ResponseInterface).
     *
     * @param array $headers
     *
     * @return array
     */
    public static function scrubHeaders(array $headers): array
    {
        $result = [];

        foreach ($headers as $key => $value) {
            $lower = strtolower((string) $key);

            if (in_array($lower, self::SENSITIVE_HEADERS, true)) {
                $value = is_array($value) ? [self::MASK] : self::MASK;
            }

            $result[$lower] = $value;
        }

        return $result;
    }

    /**
     * Mask values of sensitive query parameters in a URI string.
     *
     * @param string $uri
     *
     * @return string
     */
    public static function scrubUri(string $uri): string
    {
        $parts = parse_url($uri);

        if (empty($parts['query'])) {
            return $uri;
        }

        parse_str($parts['query'], $params);

        foreach ($params as $key => $value) {
            if (self::isSensitiveKey((string) $key)) {
                $params[$key] = self::MASK;
            }
        }

        $queryParts = [];

        foreach ($params as $key => $value) {
            // Keep the mask readable in logs; other values are percent-encoded normally.
            $encodedValue = ($value === self::MASK) ? self::MASK : rawurlencode((string) $value);
            $queryParts[] = rawurlencode((string) $key) . '=' . $encodedValue;
        }

        $parts['query'] = implode('&', $queryParts);

        return self::buildUrl($parts);
    }

    /**
     * Recursively scrub sensitive keys from a decoded array (e.g. a JSON body).
     *
     * @param array $data
     *
     * @return array
     */
    public static function scrubArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recurse into nested arrays, e.g. for JSON bodies with multiple levels of nesting.
                $data[$key] = self::scrubArray($value);
            } elseif (self::isSensitiveKey((string) $key)) {
                $data[$key] = self::MASK;
            }
        }

        return $data;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private static function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);

        foreach (self::SENSITIVE_KEY_PATTERNS as $pattern) {
            // @TODO PHP 8.0+: replace strpos() !== false with str_contains()
            if (strpos($lower, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reconstruct a URL from the array returned by parse_url().
     *
     * @param array $parts
     *
     * @return string
     */
    private static function buildUrl(array $parts): string
    {
        $url = '';

        if (isset($parts['scheme'])) {
            $url .= $parts['scheme'] . '://';
        }

        if (isset($parts['host'])) {
            $url .= $parts['host'];
        }

        if (isset($parts['port'])) {
            $url .= ':' . $parts['port'];
        }

        if (isset($parts['path'])) {
            $url .= $parts['path'];
        }

        if (isset($parts['query'])) {
            $url .= '?' . $parts['query'];
        }

        if (isset($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }

        return $url;
    }
}
