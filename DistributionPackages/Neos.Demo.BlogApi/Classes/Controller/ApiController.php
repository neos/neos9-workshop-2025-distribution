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
use Neos\Neos\FrontendRouting\NodeUriBuilderFactory;
use Neos\Neos\FrontendRouting\Options;
use Psr\Http\Message\ResponseInterface;

final class ApiController implements ControllerInterface
{
    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected NodeUriBuilderFactory $nodeUriBuilderFactory;

    public function getPostDetails(ActionRequest $request): QueryResponse
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

        try {
            $subgraph = $contentRepository->getContentSubgraph($nodeAddress->workspaceName, $nodeAddress->dimensionSpacePoint);
            // manually
            // $subgraph = $contentGraph->getSubgraph($nodeAddress->dimensionSpacePoint, NeosVisibilityConstraints::excludeRemoved()->merge(NeosVisibilityConstraints::excludeDisabled()));
        } catch (WorkspaceDoesNotExist $workspaceDoesNotExist) {
            return QueryResponse::clientError(sprintf('Workspace %s does not exist', $nodeAddress->workspaceName->value));
        } catch (AccessDenied $accessDenied) {
            return QueryResponse::clientError($accessDenied->getMessage());
        }

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
        } catch (\Exception $e) {
            return QueryResponse::serverError($e)->toHttpResponse();
        }
    }
}
