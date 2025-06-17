<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter;

use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;

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
        echo $this->contentRepository->id;
        foreach ($this->importEventProvider->read() as $event) {
            // @todo: implement me
        }
    }
}
