<?php

declare(strict_types=1);

use Behat\Behat\Context\Context as BehatContext;
use Neos\Behat\FlowBootstrapTrait;
use Neos\ContentRepository\TestSuite\Behavior\Features\Bootstrap\CRTestSuiteTrait;
use Neos\ContentRepositoryRegistry\TestSuite\Behavior\CRRegistrySubjectProvider;

class FeatureContext implements BehatContext
{
    use FlowBootstrapTrait;
    use CRTestSuiteTrait;
    use CRRegistrySubjectProvider;
    use BlogApiTrait;

    public function __construct()
    {
        self::bootstrapFlow();
        $this->setUpCRRegistry();
    }
}
