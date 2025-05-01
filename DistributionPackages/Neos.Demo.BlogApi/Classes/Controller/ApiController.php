<?php

declare(strict_types=1);

namespace Neos\Demo\BlogApi\Controller;

use GuzzleHttp\Psr7\Response;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAddress;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerInterface;
use Neos\Flow\Mvc\Exception\NoSuchActionException;
use Psr\Http\Message\ResponseInterface;

final class ApiController implements ControllerInterface
{
    public function getPostDetails(ActionRequest $request): ResponseInterface
    {
        $node = $request->getHttpRequest()->getQueryParams()['node'] ?? null;
        if (!is_string($node)) {
            return new Response(
                status: 400,
                body: json_encode([
                    'error' => [
                        'message' => 'No node address specified'
                    ]
                ])
            );
        }

        try {
            $nodeAddress = NodeAddress::fromJsonString($node);
        } catch (\InvalidArgumentException $e) {
            return new Response(
                status: 400,
                body: json_encode([
                    'error' => [
                        'message' => sprintf('Not a valid node address: %s' , $e->getMessage())
                    ]
                ])
            );
        }

        return new Response(
            status: 200,
            body: json_encode([
                'success' => [
                    'node' => $nodeAddress->aggregateId
                ]
            ])
        );
    }

    public function processRequest(ActionRequest $request): ResponseInterface
    {
        return match ($request->getControllerActionName()) {
            'getPostDetails' => $this->getPostDetails($request),
            default => throw new NoSuchActionException(sprintf('An action "%s" does not exist in controller "%s".', $request->getControllerActionName(), self::class), 1746104348)
        };
    }
}
