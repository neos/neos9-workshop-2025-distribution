<?php

declare(strict_types=1);

use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\Demo\BlogImporter\Importer;

trait StandaloneBlogImporterTrait
{
   // /**
   //  * @When /I import file "([^"]*)" into blog "([^"]*)"/
   //  */
   // public function iImportFileIntoBlog(string $filename, string $blogId): void
   // {
   //     $subject = $this->getObject(Importer::class);
   //     $subject->importFile($filename, NodeAggregateId::fromString($blogId));
   // }

    /**
     * @template T of object
     * @param class-string<T> $className
     *
     * @return T
     */
    abstract private function getObject(string $className): object;
}
