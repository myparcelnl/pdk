<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Exception;

use Throwable;

class ModelNotFoundException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $modelClassName;

    /**
     * @var array
     */
    protected $ids;

    /**
     * @param  string  $modelClassName
     * @param  array  $ids
     * @param  int  $code
     * @param  null|Throwable  $previous
     */
    public function __construct(string $modelClassName, array $ids = [], int $code = 0, ?Throwable $previous = null)
    {
        $this->modelClassName = $modelClassName;
        $this->ids = $ids;

        $message = "No query results for model [{$modelClassName}]";

        if (! empty($ids)) {
            $message .= ' with ID(s): ' . implode(', ', $ids);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getModelClassName(): string
    {
        return $this->modelClassName;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param  string  $modelClassName
     *
     * @return $this
     */
    public function setModelClassName(string $modelClassName): self
    {
        $this->modelClassName = $modelClassName;
        $this->message = "No query results for model [{$modelClassName}]";

        if (! empty($this->ids)) {
            $this->message .= ' with ID(s): ' . implode(', ', $this->ids);
        }

        return $this;
    }
}
