<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter;

final class FakePublicationEventProvider implements PublicationEventProviderInterface
{
    /**
     * @param iterable<BlogPostingWasPublished> $events
     */
    public function __construct(
        private iterable $events
    ) {
    }

    /**
     * @return iterable<BlogPostingWasPublished>
     */
    public function read(): iterable
    {
        return $this->events;
    }
}
