<?php

declare(strict_types=1);

namespace Neos\Demo\BlogImporter;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class BlogPostingWasPublished
{
    public function __construct(
        public string $id,
        public ?string $language,
        public string $headline,
        public string $abstract,
        public \DateTimeImmutable $datePublished,
        public string $author,
    ) {
    }
}
