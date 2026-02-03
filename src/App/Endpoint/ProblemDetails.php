<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

/**
 * RFC 9457 compliant Problem Details value object.
 *
 * Represents a machine-readable format for specifying errors in HTTP API responses.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9457.html
 */
final class ProblemDetails implements Arrayable
{
    /**
     * @var string|null A URI reference that identifies the problem type
     */
    private $type;

    /**
     * @var string|null A short, human-readable summary of the problem type
     */
    private $title;

    /**
     * @var int|null The HTTP status code
     */
    private $status;

    /**
     * @var string|null A human-readable explanation specific to this occurrence
     */
    private $detail;

    /**
     * @var string|null A URI reference that identifies the specific occurrence
     */
    private $instance;

    /**
     * @var array Extension members (additional problem-specific properties)
     */
    private $extensions;

    /**
     * @param  string|null $type       URI reference identifying the problem type (default: 'about:blank')
     * @param  string|null $title Short human-readable summary
     * @param  int|null $status        HTTP status code
     * @param  string|null $detail Human-readable explanation
     * @param  string|null $instance URI reference identifying this occurrence
     */
    public function __construct(
        ?string $type = null,
        ?string $title = null,
        ?int $status = null,
        ?string $detail = null,
        ?string $instance = null
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->status = $status;
        $this->detail = $detail;
        $this->instance = $instance;
        $this->extensions = [];
    }

    /**
     * Get the problem type URI.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get the problem title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Get the problem detail.
     */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * Get the instance URI.
     */
    public function getInstance(): ?string
    {
        return $this->instance;
    }

    /**
     * Create a new ProblemDetails instance with an extension member.
     *
     * This method follows an immutable pattern, returning a new instance
     * with the extension member added.
     *
     * @param  string $key   Extension member key
     * @param  mixed  $value Extension member value
     *
     * @return self New instance with the extension member
     */
    public function with(string $key, $value): self
    {
        $new = clone $this;
        $new->extensions[$key] = $value;

        return $new;
    }

    /**
     * Convert the ProblemDetails to an array following RFC 9457.
     *
     * @return array<string, mixed>
     */
    public function toArray(?int $flags = null): array
    {
        if ($flags !== null) {
            throw new \InvalidArgumentException('Flags are not supported for ProblemDetails toArray method.');
        }

        $data = [
            'type'   => $this->type,
            'status' => $this->status,
        ];

        // Add optional standard members if they are set
        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->detail !== null) {
            $data['detail'] = $this->detail;
        }

        if ($this->instance !== null) {
            $data['instance'] = $this->instance;
        }

        // Add extension members
        foreach ($this->extensions as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }
}
