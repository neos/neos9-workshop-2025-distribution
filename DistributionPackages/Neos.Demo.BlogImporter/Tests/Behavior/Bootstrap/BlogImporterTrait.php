<?php

declare(strict_types=1);

use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\Demo\BlogImporter\Importer;
use Neos\Demo\BlogImporter\FsCsvPublicationEventProvider;

trait BlogImporterTrait
{
    /** @phpstan-ignore constant.notFound */
    const FIXTURE_PATH = __DIR__ . '/../Fixtures/';

    /**
     * @When /I import file "([^"]*)" into blog "([^"]*)"/
     */
    public function iImportFileIntoBlog(string $filename, string $blogId): void
    {
        $subject = new Importer(
            importEventProvider: new FsCsvPublicationEventProvider(self::FIXTURE_PATH . $filename . '.csv'),
            contentRepository: $this->currentContentRepository
        );
        $subject->run(NodeAggregateId::fromString($blogId));
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     *
     * @return T
     */
    abstract private function getObject(string $className): object;
}
