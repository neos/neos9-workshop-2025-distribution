<?php

/*
 * This script belongs to the package "Sitegeist.Archaeopteryx".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

declare(strict_types=1);

namespace Neos\Demo\BlogApi\Controller;

use GuzzleHttp\Psr7\Response;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\ResponseInterface;

#[Flow\Proxy(false)]
final class QueryResponseHelper
{
    private const STATUS_CODE_SUCCESS = 200;
    private const STATUS_CODE_CLIENT_ERROR = 400;

    private const DISCRIMINATOR_SUCCESS = 'success';
    private const DISCRIMINATOR_ERROR = 'error';

    private function __construct()
    {
    }

    /**
     * @param array<mixed>|\JsonSerializable $payload
     */
    public static function success(array|\JsonSerializable $payload): ResponseInterface
    {
        return self::toHttpResponse(
            statusCode: self::STATUS_CODE_SUCCESS,
            discriminator: self::DISCRIMINATOR_SUCCESS,
            payload: $payload,
        );
    }

    public static function clientError(string $cause): ResponseInterface
    {
        return self::toHttpResponse(
            statusCode: self::STATUS_CODE_CLIENT_ERROR,
            discriminator: self::DISCRIMINATOR_ERROR,
            payload: [
                'message' => $cause,
            ]
        );
    }

    private static function toHttpResponse(int $statusCode, string $discriminator, array|\JsonSerializable $payload): ResponseInterface
    {
        return new Response(
            status: $statusCode,
            headers: [
                'Content-Type' => 'application/json'
            ],
            body: json_encode(
                [$discriminator => $payload],
                JSON_THROW_ON_ERROR
            )
        );
    }
}
