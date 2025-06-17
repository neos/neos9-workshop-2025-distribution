<?php

namespace Neos\Demo\BlogImporter;

interface PublicationEventProviderInterface
{
    /**
     * @return iterable<BlogPostingWasPublished>
     */
    public function read(): iterable;
}
