<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter;

use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;

class Importer
{
    public function importFile(string $filename, NodeAggregateId $blogId): void
    {
        foreach (PublicationEventProvider::readFromFile($filename) as $event) {
            // @todo: implement me
        }
    }
}
