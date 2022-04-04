<?php

/**
 * PHP Version 8
 *
 * Lambda Service File
 *
 * @category Service
 * @package  App\Service
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

/**
 * Lambda Command class
 */
class LambdaService
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

    /**
     * Get Next Request from AWS Lambda Event Pool
     */
    final public function getNextRequest(): array
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
    final public function sendResponse(string $invocationId, string $response): void
    {
        $this->httpClient->request(
            'POST',
            self::BASE_API_URI . "/{$invocationId}/response",
            ['body' => $response]
        );
    }
}
