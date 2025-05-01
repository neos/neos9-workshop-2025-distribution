<?php

declare(strict_types=1);

namespace Neos\Demo\BlogApi\Controller;

use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\Security\Exception\AccessDenied;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\SharedModel\Exception\WorkspaceDoesNotExist;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAddress;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerInterface;
use Neos\Flow\Mvc\Exception\NoSuchActionException;
use Neos\Neos\FrontendRouting\NodeUriBuilderFactory;
use Neos\Neos\FrontendRouting\Options;
use Neos\Neos\FrontendRouting\SiteDetection\SiteDetectionResult;
use Psr\Http\Message\ResponseInterface;

final class ApiController implements ControllerInterface
{
    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected NodeUriBuilderFactory $nodeUriBuilderFactory;

    private function getPostDetails(ActionRequest $request): QueryResponse
    {
        $nodeAddressSerialized = $request->getHttpRequest()->getQueryParams()['node'] ?? null;
        if (!is_string($nodeAddressSerialized)) {
            return QueryResponse::clientError('No node address specified');
        }

        try {
            $nodeAddress = NodeAddress::fromJsonString($nodeAddressSerialized);
        } catch (\InvalidArgumentException $e) {
            return QueryResponse::clientError(sprintf('Not a valid node address: %s' , $e->getMessage()));
        }

        $contentRepository = $this->contentRepositoryRegistry->get($nodeAddress->contentRepositoryId);

        $subgraph = $contentRepository->getContentSubgraph($nodeAddress->workspaceName, $nodeAddress->dimensionSpacePoint);
        // manually
        // $subgraph = $contentGraph->getSubgraph($nodeAddress->dimensionSpacePoint, NeosVisibilityConstraints::excludeRemoved()->merge(NeosVisibilityConstraints::excludeDisabled()));

        $node = $subgraph->findNodeById($nodeAddress->aggregateId);
        if ($node === null) {
            return QueryResponse::clientError(sprintf('Node address %s does not exist in subgraph.' , $nodeAddress->toJson()));
        }

        if ($node->nodeTypeName->value !== 'Neos.Demo:Document.BlogPosting') {
            return QueryResponse::clientError(sprintf('Node %s is not a blog posting.' , $nodeAddress->toJson()));
        }

        $nodeUriBuilder = $this->nodeUriBuilderFactory->forActionRequest($request);
        $uri = $nodeUriBuilder->uriFor(NodeAddress::fromNode($node), Options::createForceAbsolute());

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

        return QueryResponse::success([
            'title' => $title,
            'abstract' => $abstract,
            'datePublished' => $datePublished,
            'authorName' => $authorName,
            'uri' => $uri,
        ]);
    }

    public function processRequest(ActionRequest $request): ResponseInterface
    {
        try {
            return match ($request->getControllerActionName()) {
                'getPostDetails' => $this->getPostDetails($request)->toHttpResponse(),
                default => throw new NoSuchActionException(sprintf('An action "%s" does not exist in controller "%s".', $request->getControllerActionName(), self::class), 1746104348)
            };
        } catch (WorkspaceDoesNotExist $workspaceDoesNotExist) {
            return QueryResponse::clientError($workspaceDoesNotExist)->toHttpResponse();
        } catch (AccessDenied $accessDenied) {
            return QueryResponse::clientError($accessDenied)->toHttpResponse();
        } catch (\Exception $e) {
            return QueryResponse::serverError($e)->toHttpResponse();
        }
    }
}
