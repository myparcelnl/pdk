<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Exception;

use Exception;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use Throwable;

class ApiException extends Exception
{
    /**
     * @var array
     */
    private $errors;

    /**
     * @var null|string
     */
    private $bodyMessage;

    /**
     * @var string|null
     */
    private $requestId;

    /**
     * @var \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    private $response;

    /**
     * @param  \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface $response
     * @param  int                                                  $code
     * @param  \Throwable|null                                      $previous
     */
    public function __construct(ClientResponseInterface $response, int $code = 0, Throwable $previous = null)
    {
        $body = json_decode($response->getBody(), true);

        $this->response    = $response;
        $this->errors      = $body['errors'] ?? [];
        $this->requestId   = $body['request_id'] ?? null;
        $this->bodyMessage = $body['message'] ?? $body['Message'] ?? null;

        parent::__construct(
            sprintf(
                'Request failed. Status code: %s. Message: %s',
                $response->getStatusCode(),
                $this->bodyMessage ?? ''
            ),
            $code,
            $previous
        );
    }

    /**
     * The "message" the api returned, without the request context this exception adds to its own
     * message. Use it to show the user what went wrong.
     *
     * @return null|string
     */
    public function getBodyMessage(): ?string
    {
        return $this->bodyMessage;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * The readable messages in the errors of this exception. The api reports validation errors as
     * an array keyed by error code, with the readable messages in a "human" array. Other errors
     * only have a "message" or a "title".
     *
     * @return string[]
     */
    public function getHumanMessages(): array
    {
        $messages = [];

        foreach ($this->errors as $error) {
            if (! is_array($error)) {
                continue;
            }

            $messages = array_merge($messages, self::extractMessages($error));
        }

        return array_values(array_unique($messages));
    }

    /**
     * @param  array $error
     *
     * @return string[]
     */
    private static function extractMessages(array $error): array
    {
        foreach (['human', 'message', 'title'] as $key) {
            if (isset($error[$key])) {
                return array_filter((array) $error[$key], 'is_string');
            }
        }

        $messages = [];

        // Validation errors are keyed by their error code, so look one level deeper.
        foreach ($error as $details) {
            if (is_array($details)) {
                $messages = array_merge($messages, self::extractMessages($details));
            }
        }

        return $messages;
    }

    /**
     * @return null|string
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    public function getResponse(): ClientResponseInterface
    {
        return $this->response;
    }
}
