<?php

declare(strict_types=1);

namespace Neos\Demo\BlogApi\Controller;

use GuzzleHttp\Psr7\Response;
use Neos\ContentRepository\Core\Feature\Security\Exception\AccessDenied;
use Neos\ContentRepository\Core\SharedModel\Exception\WorkspaceDoesNotExist;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAddress;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerInterface;
use Neos\Flow\Mvc\Exception\NoSuchActionException;
use Psr\Http\Message\ResponseInterface;

final class ApiController implements ControllerInterface
{
    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    public function getPostDetails(ActionRequest $request): ResponseInterface
    {
        $nodeAddressSerialized = $request->getHttpRequest()->getQueryParams()['node'] ?? null;
        if (!is_string($nodeAddressSerialized)) {
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
            $nodeAddress = NodeAddress::fromJsonString($nodeAddressSerialized);
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

        $contentRepository = $this->contentRepositoryRegistry->get($nodeAddress->contentRepositoryId);
        // $contentGraph->getSubgraph($nodeAddress->dimensionSpacePoint, NeosVisibilityConstraints::excludeRemoved()->merge(NeosVisibilityConstraints::excludeDisabled()));

        try {
            $subgraph = $contentRepository->getContentSubgraph($nodeAddress->workspaceName, $nodeAddress->dimensionSpacePoint);
        } catch (WorkspaceDoesNotExist $workspaceDoesNotExist) {
        } catch (AccessDenied $accessDenied) {
        }

        $node = $subgraph->findNodeById($nodeAddress->aggregateId);
        if ($node === null) {
            return new Response(
                status: 400,
                body: json_encode([
                    'error' => [
                        'message' => sprintf('Node address %s does not exist in subgraph.' , $nodeAddress->toJson())
                    ]
                ])
            );
        }

        if ($node->nodeTypeName->value !== 'Neos.Demo:Document.BlogPosting') {
            return new Response(
                status: 400,
                body: json_encode([
                    'error' => [
                        'message' => sprintf('Node %s is not a blog posting.' , $nodeAddress->toJson())
                    ]
                ])
            );
        }

        $title = $node->getProperty('title');
        $abstract = $node->getProperty('abstract');
        if ($abstract) {
            $abstract = strip_tags($abstract);
        }
        $datePublished = $node->getProperty('datePublished');
        if ($datePublished instanceof \DateTimeImmutable) {
            $datePublished = $datePublished->format(\DateTimeImmutable::ATOM);
        }
        $authorName = $node->getProperty('authorName');

        return new Response(
            status: 200,
            body: json_encode([
                'success' => [
                    'title' => $title,
                    'abstract' => $abstract,
                    'datePublished' => $datePublished,
                    'authorName' => $authorName,
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
