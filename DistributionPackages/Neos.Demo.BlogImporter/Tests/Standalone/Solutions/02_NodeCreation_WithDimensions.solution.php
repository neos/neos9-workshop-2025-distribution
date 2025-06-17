<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter;

use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNode;
use Neos\ContentRepository\Core\Feature\NodeModification\Dto\PropertyValuesToWrite;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class Importer
{
    public function __construct(
        private readonly PublicationEventProviderInterface $importEventProvider,
        private readonly ContentRepository $contentRepository,
    ) {
    }

    public function run(NodeAggregateId $blogId): void
    {
        foreach ($this->importEventProvider->read() as $event) {
            $this->contentRepository->handle(CreateNodeAggregateWithNode::create(
                workspaceName: WorkspaceName::forLive(),
                nodeAggregateId: NodeAggregateId::fromString(sprintf('demo-neos-%s', $event->id)),
                nodeTypeName: NodeTypeName::fromString('Neos.Demo:Document.BlogPosting'),
                originDimensionSpacePoint: $event->language ? OriginDimensionSpacePoint::fromArray(['language' => $event->language]) : OriginDimensionSpacePoint::createWithoutDimensions(),
                parentNodeAggregateId: $blogId,
                initialPropertyValues: PropertyValuesToWrite::fromArray([
                    'title' => $event->headline,
                    'abstract' => $event->abstract,
                    'datePublished' => $event->datePublished,
                    'authorName' => $event->author,
                ])
            ));
        }
    }
}
