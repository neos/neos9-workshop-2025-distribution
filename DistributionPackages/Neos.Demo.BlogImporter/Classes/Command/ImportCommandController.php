<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter\Command;

use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Demo\BlogImporter\FsCsvPublicationEventProvider;
use Neos\Demo\BlogImporter\Importer;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Annotations as Flow;

class ImportCommandController extends CommandController
{
    #[Flow\Inject()]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    public function runCommand(string $filename, string $blogId, string $contentRepository = 'default', ): void
    {
        $this->outputLine('Start');
        $importer = new Importer(
            importEventProvider: new FsCsvPublicationEventProvider($filename),
            contentRepository: $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString($contentRepository))
        );
        $importer->run(NodeAggregateId::fromString($blogId));
        $this->outputLine('Finished');
    }
}
