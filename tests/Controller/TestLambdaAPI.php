<?php

/**
 * PHP Version 8
 *
 * Lambda API Test File
 *
 * @category Tests
 * @package  App\Tests
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Lambda API Test class
 */
class TestLambdaAPI
{
    public const TEST_PAYLOAD = [
        'test' => true,
        'integer' => 123,
        'payload' => [
            'url' => 'http://example.com/lambda/request',
            'content' => 'EMPTY',
        ],
    ];

    public const BASE_DIR_HASHES = __DIR__ . '/../hashes';

    /**
     * Get Next Request
     */
    final public function next(): JsonResponse
    {
        $invocationId = $this->generateHash();

        return new JsonResponse(
            self::TEST_PAYLOAD,
            JsonResponse::HTTP_OK,
            ['lambda-runtime-aws-request-id' => $invocationId]
        );
    }

    /**
     * Post Response back
     */
    final public function response(string $invocationId, Request $request): JsonResponse
    {
        if (!file_exists(self::BASE_DIR_HASHES . "/{$invocationId}.test")) {
            return new JsonResponse(['error' => 'Invalid Invocation ID'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $content = json_decode($request->getContent(), true);
        if (!empty(array_diff($content, self::TEST_PAYLOAD))) {
            return new JsonResponse(['error' => 'Invalid Echo Payload'], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse();
    }

    /**
     * Generate New Hash (Invocation ID)
     */
    private function generateHash(): string
    {
        mkdir(self::BASE_DIR_HASHES, 0775, true);

        if (!empty(glob(TestLambdaAPI::BASE_DIR_HASHES . '/*'))) {
            return '';
        }

        $newHash = hash('sha1', (string) time());
        file_put_contents(self::BASE_DIR_HASHES . "/{$newHash}.test", $newHash);

        return $newHash;
    }
}
