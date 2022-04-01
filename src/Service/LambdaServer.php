<?php

/**
 * PHP Version 8
 *
 * Lambda Server File
 *
 * @category Server
 * @package  App\Service
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  MIT /LICENSE
 */

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

/**
 * Lambda Command class
 */
class LambdaServer
{
    private const BASE_API_URI = '/2018-06-01/runtime/invocation';

    /** @var Symfony\Contracts\HttpClient\HttpClientInterface $httpClient */
    private $httpClient;

    /**
     * Initialize Dependencies
     */
    public function __construct(string $baseDomain)
    {
        $this->httpClient = HttpClient::createForBaseUri("http://{$baseDomain}");
    }

    /**
     * Get Next Request from AWS Lambda Event Pool
     */
    public function getNextRequest(): array
    {
        $response = $this->httpClient->request('GET', self::BASE_API_URI . '/next');

        return [
            'invocationId' => $response->getHeaders()['lambda-runtime-aws-request-id'][0],
            'payload' => json_decode((string) $response->getContent(), true),
        ];
    }

    /**
     * Send Response back to AWS Lambda Event Pool
     */
    public function sendResponse(string $invocationId, string $response): void
    {
        $this->httpClient->request(
            'POST',
            self::BASE_API_URI . "/{$invocationId}/response",
            ['body' => $response]
        );
    }

    /**
     * Handle AWS Lambda Event
     */
    public function handle(array $payload): string
    {
        // Just an echo, as example.
        $response = json_encode($payload);

        /**
         * Implement your function here !!
         */

        return $response;
    }
}
