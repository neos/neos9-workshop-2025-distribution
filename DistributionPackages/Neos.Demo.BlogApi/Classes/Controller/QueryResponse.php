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
final class QueryResponse
{
    private const STATUS_CODE_SUCCESS = 200;
    private const STATUS_CODE_CLIENT_ERROR = 400;
    private const STATUS_CODE_SERVER_ERROR = 500;

    private const DISCRIMINATOR_SUCCESS = 'success';
    private const DISCRIMINATOR_ERROR = 'error';

    /**
     * @param array<mixed>|\JsonSerializable $payload
     */
    private function __construct(
        private readonly int $statusCode,
        private readonly string $discriminator,
        private readonly array|\JsonSerializable $payload,
    ) {
    }

    /**
     * @param array<mixed>|\JsonSerializable $payload
     */
    public static function success(array|\JsonSerializable $payload): self
    {
        return new self(
            statusCode: self::STATUS_CODE_SUCCESS,
            discriminator: self::DISCRIMINATOR_SUCCESS,
            payload: $payload,
        );
    }

    public static function clientError(\Exception|string $cause): self
    {
        return new self(
            statusCode: self::STATUS_CODE_CLIENT_ERROR,
            discriminator: self::DISCRIMINATOR_ERROR,
            payload: is_string($cause) ? [
                'message' => $cause,
            ] : [
                'type' => $cause::class,
                'code' => $cause->getCode(),
                'message' => $cause->getMessage(),
            ],
        );
    }

    public static function serverError(\Exception $cause): self
    {
        return new self(
            statusCode: self::STATUS_CODE_SERVER_ERROR,
            discriminator: self::DISCRIMINATOR_ERROR,
            payload: [
                'type' => $cause::class,
                'code' => $cause->getCode(),
                'message' => $cause->getMessage(),
            ],
        );
    }

    public function toHttpResponse(): ResponseInterface
    {
        return new Response(
            status: $this->statusCode,
            headers: [
                'Content-Type' => 'application/json'
            ],
            body: json_encode(
                [$this->discriminator => $this->payload],
                JSON_THROW_ON_ERROR
            )
        );
    }
}
