<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter;

use League\Csv\Reader;

final class FsCsvPublicationEventProvider implements PublicationEventProviderInterface
{
    public function __construct(
        private readonly string $filename
    ) {
    }

    /**
     * @return iterable<BlogPostingWasPublished>
     */
    public function read(): iterable
    {
        $reader = Reader::createFromPath($this->filename);
        $reader->setHeaderOffset(0);
        $reader->setDelimiter(';');
        foreach ($reader->getRecords() as $record) {
            yield new BlogPostingWasPublished(
                $record['id'],
                $record['language'] ?: null,
                $record['headline'],
                $record['abstract'],
                \DateTimeImmutable::createFromFormat(\DateTimeImmutable::W3C, $record['datePublished']) ?: throw new \RuntimeException(sprintf('Date %s is not valid', $record['datePublished']), 1747205914),
                $record['author'],
            );
        }
    }
}
