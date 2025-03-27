<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handler for CORS related functionality
 */
class CorsHandler
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Handle CORS preflight request
     *
     * @param Request $request
     * @return Response|null
     */
    public function handlePreflight(Request $request): ?Response
    {
        if ($request->getMethod() !== 'OPTIONS') {
            return null;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $this->addCorsHeaders($request, $response);
        return $response;
    }

    /**
     * Add CORS headers to response
     *
     * @param Request  $request
     * @param Response $response
     */
    public function addCorsHeaders(Request $request, Response $response): void
    {
        $origin = $request->headers->get('Origin');
        
        if (!$origin) {
            return;
        }

        // Check if origin is allowed
        if (in_array('*', $this->options['allowedOrigins'])) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } elseif (in_array($origin, $this->options['allowedOrigins'])) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } elseif (in_array('self', $this->options['allowedOrigins'])) {
            $response->headers->set('Access-Control-Allow-Origin', $request->getSchemeAndHttpHost());
        }

        if ($request->getMethod() === 'OPTIONS') {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->options['allowedMethods']));
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->options['allowedHeaders']));
            
            if ($this->options['maxAge']) {
                $response->headers->set('Access-Control-Max-Age', $this->options['maxAge']);
            }
        }

        if ($this->options['supportsCredentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->options['exposedHeaders']) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->options['exposedHeaders']));
        }
    }
} 