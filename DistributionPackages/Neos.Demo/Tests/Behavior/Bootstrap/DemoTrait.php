<?php

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\InternalRequestEngine;
use Neos\Flow\Security\Context;
use Neos\Utility\ObjectAccess;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

trait DemoTrait
{
    /**
     * @template T of object
     * @param class-string<T> $className
     *
     * @return T
     */
    abstract private function getObject(string $className): object;

    /**
     * @When I run this scenario
     */
    public function iRunThisScenario(): void
    {
        Assert::assertTrue(true);
    }
}
