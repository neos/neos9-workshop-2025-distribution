<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter;

use League\Csv\Reader;

class PublicationEventProvider
{
    /** @phpstan-ignore constant.notFound */
    const FIXTURE_PATH = FLOW_PATH_ROOT . 'DistributionPackages/Neos.Demo.BlogImporter/Tests/Behavior/Fixtures/';

    /**
     * @return iterable<BlogPostingWasPublished>
     */
    public static function readFromFile(string $filename): iterable
    {
        $reader = Reader::createFromPath(self::FIXTURE_PATH . $filename . '.csv');
        $reader->setHeaderOffset(0);
        $reader->setDelimiter(';');
        foreach ($reader->getRecords() as $record) {
            yield new BlogPostingWasPublished(
                $record['id'],
                $record['language'],
                $record['headline'],
                $record['abstract'],
                \DateTimeImmutable::createFromFormat('Y-m-d', $record['datePublished']) ?: throw new \RuntimeException(sprintf('Date %s is not valid', $record['datePublished']), 1747205914),
                $record['author'],
            );
        }
    }
}
