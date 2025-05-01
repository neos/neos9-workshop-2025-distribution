<?php

use GuzzleHttp\Psr7\Uri;
use Neos\Behat\FlowEntitiesTrait;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Domain\Model\Domain;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;

trait NeosSiteTrait
{
    use FlowEntitiesTrait;

    /**
     * @template T of object
     * @param class-string<T> $className
     *
     * @return T
     */
    abstract private function getObject(string $className): object;

    /**
     * @Given A site exists for node name :nodeName and domain :domain and package :package
     */
    public function theSiteExists(string $nodeName, string $domain, string $package): void
    {
        $siteRepository = $this->getObject(SiteRepository::class);
        $persistenceManager = $this->getObject(PersistenceManagerInterface::class);

        $site = new Site($nodeName);
        $site->setSiteResourcesPackageKey($package);
        $site->setState(Site::STATE_ONLINE);
        $siteRepository->add($site);

        $domainUri = new Uri($domain);
        $domainModel = new Domain();
        $domainModel->setHostname($domainUri->getHost());
        $domainModel->setPort($domainUri->getPort());
        $domainModel->setScheme($domainUri->getScheme());
        $domainModel->setSite($site);
        $domainRepository = $this->getObject(DomainRepository::class);
        $domainRepository->add($domainModel);

        $persistenceManager->persistAll();
    }
}
