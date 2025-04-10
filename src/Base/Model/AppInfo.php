<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

/**
 * @property string $name
 * @property string $title
 * @property string $version
 * @property string $path
 * @property string $url
 */
class AppInfo extends Model
{
    public    $attributes = [
        'name'    => null,
        'title'   => null,
        'version' => null,
        'path'    => null,
        'url'     => null,
    ];

    protected $casts      = [
        'name'    => 'string',
        'title'   => 'string',
        'version' => 'string',
        'path'    => 'string',
        'url'     => 'string',
    ];

    /**
     * @param  string $path
     *
     * @return string
     */
    public function createPath(string $path): string
    {
        $pattern = sprintf('/\%s+/', DIRECTORY_SEPARATOR);

        return preg_replace($pattern, DIRECTORY_SEPARATOR, "$this->path/$path");
    }
}
