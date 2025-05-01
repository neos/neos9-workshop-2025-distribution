<?php

declare(strict_types=1);

namespace Neos\Demo\BlogApi\Controller;

use GuzzleHttp\Psr7\Uri;
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
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Neos\FrontendRouting\NodeUriBuilder;
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

    private NodeUriBuilder $nodeUriBuilder;

    private UriBuilder $uriBuilder;

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

        // instance of check via node type manager

        $uri = $this->nodeUriBuilder->uriFor(NodeAddress::fromNode($node), Options::createForceAbsolute());

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

    private function getPostListing(ActionRequest $request): QueryResponse
    {
        $httpRequest = $request->getHttpRequest();
        $blogId = $httpRequest->getQueryParams()['blogId'] ?? null;
        $language = $httpRequest->getQueryParams()['language'] ?? null;
        if (!is_string($blogId) && !is_string($language)) {
            return QueryResponse::clientError('No blogId or language specified');
        }

        $contentRepositoryId = SiteDetectionResult::fromRequest($httpRequest)->contentRepositoryId;

        $contentRepository = $this->contentRepositoryRegistry->get($contentRepositoryId);

        $subgraph = $contentRepository->getContentSubgraph(WorkspaceName::forLive(), DimensionSpacePoint::fromArray([
            'language' => $language
        ]));

        $blogNode = $subgraph->findNodeById(NodeAggregateId::fromString($blogId));
        if ($blogNode === null) {
            return QueryResponse::clientError(sprintf('Blog %s does not exist in subgraph %s %s.' , $blogId, $subgraph->getWorkspaceName()->value, $subgraph->getDimensionSpacePoint()->toJson()));
        }

        $baseUri = new Uri($this->uriBuilder->uriFor(actionName: 'getPostDetails', controllerName: 'Api', packageKey: 'Neos.Demo.BlogApi'));

        $postings = [];
        foreach ($subgraph->findChildNodes($blogNode->aggregateId, FindChildNodesFilter::create(nodeTypes: 'Neos.Demo:Document.BlogPosting')) as $blogPositingNode) {
            $postings[] = [
                'id' => $blogPositingNode->aggregateId->value,
                'title' => $blogPositingNode->getProperty('title'),
                'api' => $baseUri->withQuery(http_build_query(['node' => NodeAddress::fromNode($blogPositingNode)->toJson()]))
            ];
        }

        $blogUri = $this->nodeUriBuilder->uriFor(NodeAddress::fromNode($blogNode), Options::createForceAbsolute());

        return QueryResponse::success([
            'title' => $blogNode->getProperty('title'),
            'postings' => $postings,
            'blogUri' => $blogUri,
        ]);
    }

    public function processRequest(ActionRequest $request): ResponseInterface
    {
        $this->uriBuilder = new UriBuilder();
        $this->uriBuilder->setRequest($request);
        $this->nodeUriBuilder = $this->nodeUriBuilderFactory->forActionRequest($request);
        try {
            return match ($request->getControllerActionName()) {
                'getPostDetails' => $this->getPostDetails($request)->toHttpResponse(),
                'getPostListing' => $this->getPostListing($request)->toHttpResponse(),
                default => throw new NoSuchActionException(sprintf('An action "%s" does not exist in controller "%s".', $request->getControllerActionName(), self::class), 1746104348)
            };
        } catch (WorkspaceDoesNotExist $workspaceDoesNotExist) {
            return QueryResponse::clientError($workspaceDoesNotExist)->toHttpResponse();
        } catch (AccessDenied $accessDenied) {
            return QueryResponse::clientError($accessDenied)->toHttpResponse();
        } catch (\InvalidArgumentException $e) {
            return QueryResponse::clientError($e)->toHttpResponse();
        } catch (\Exception $e) {
            return QueryResponse::serverError($e)->toHttpResponse();
        }
    }
}
