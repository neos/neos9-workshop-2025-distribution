<?php

declare(strict_types=1);

use Behat\Behat\Context\Context as BehatContext;
use Doctrine\DBAL\Connection;
use Neos\Behat\FlowBootstrapTrait;
use Neos\ContentGraph\DoctrineDbalAdapter\DoctrineDbalContentGraphProjectionFactory;
use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\Factory\CommandHooksFactory;
use Neos\ContentRepository\Core\Factory\ContentRepositoryFactory;
use Neos\ContentRepository\Core\Factory\ContentRepositoryServiceFactoryInterface;
use Neos\ContentRepository\Core\Factory\ContentRepositoryServiceInterface;
use Neos\ContentRepository\Core\Factory\ContentRepositorySubscriberFactories;
use Neos\ContentRepository\Core\Feature\Security\AuthProviderInterface;
use Neos\ContentRepository\Core\Feature\Security\Dto\UserId;
use Neos\ContentRepository\Core\Feature\Security\StaticAuthProvider;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\ArrayNormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\CollectionTypeDenormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\ScalarNormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\UriNormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\ValueObjectArrayDenormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\ValueObjectBoolDenormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\ValueObjectFloatDenormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\ValueObjectIntDenormalizer;
use Neos\ContentRepository\Core\Infrastructure\Property\Normalizer\ValueObjectStringDenormalizer as ValueObjectStringDenormalizerAlias;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentGraphProjectionFactoryInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentGraphReadModelInterface;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\Subscription\Store\SubscriptionStoreInterface;
use Neos\ContentRepository\TestSuite\Behavior\Features\Bootstrap\CRTestSuiteTrait;
use Neos\ContentRepository\TestSuite\Fakes\FakeContentDimensionSourceFactory;
use Neos\ContentRepository\TestSuite\Fakes\FakeNodeTypeManagerFactory;
use Neos\ContentRepositoryRegistry\Factory\SubscriptionStore\DoctrineSubscriptionStore;
use Neos\EventStore\DoctrineAdapter\DoctrineEventStore;
use Neos\EventStore\EventStoreInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

class FeatureContext implements BehatContext
{
    use FlowBootstrapTrait;
    use StandaloneBlogImporterTrait;
    use CRTestSuiteTrait;
    use CRBehavioralTestsSubjectProvider;

    private ContentRepositoryFactory $contentRepositoryFactory;
    private Connection $dbalConnection;

    private object $contentRepositoryRegistry;

    public function __construct()
    {
        self::bootstrapFlow();
        $this->dbalConnection = $this->getObject(Connection::class);
        $this->contentRepositoryRegistry = new class ($this->getContentRepositoryService(...))
        {
            public function __construct(private \Closure $buildService)
            {
            }

            public function buildService($id, $factory)
            {
                return ($this->buildService)($factory);
            }
        };
    }

    protected function getContentRepositoryService(
        ContentRepositoryServiceFactoryInterface $factory
    ): ContentRepositoryServiceInterface {
        return $this->contentRepositoryFactory->buildService(
            $factory
        );
    }

    protected function createContentRepository(
        ContentRepositoryId $contentRepositoryId
    ): ContentRepository {
        $clock = $this->buildClock();
        $this->contentRepositoryFactory = new ContentRepositoryFactory(
            contentRepositoryId: $contentRepositoryId,
            eventStore: $this->buildEventStore($contentRepositoryId, $clock),
            nodeTypeManager: (new FakeNodeTypeManagerFactory)->build($contentRepositoryId, []),
            contentDimensionSource: (new FakeContentDimensionSourceFactory())->build($contentRepositoryId, []),
            propertySerializer: $this->buildPropertySerializer(),
            authProviderFactory: $this->buildAuthProviderFactory(),
            clock: $clock,
            subscriptionStore: $this->buildSubscriptionStore($contentRepositoryId, $clock),
            contentGraphProjectionFactory: $this->buildContentGraphProjectionFactory(),
            contentGraphCatchUpHookFactory: null,
            commandHooksFactory: new CommandHooksFactory(),
            additionalSubscriberFactories: ContentRepositorySubscriberFactories::createEmpty(),
            logger: null
        );

        return $this->contentRepositoryFactory->getOrBuild();
    }

    private function buildEventStore(ContentRepositoryId $contentRepositoryId, ClockInterface $clock): EventStoreInterface
    {
        return new DoctrineEventStore(
            $this->dbalConnection,
            'cr_' . $contentRepositoryId->value . '_events',
            $clock
        );
    }

    private function buildSubscriptionStore(ContentRepositoryId $contentRepositoryId, ClockInterface $clock): SubscriptionStoreInterface
    {
        return new DoctrineSubscriptionStore(sprintf('cr_%s_subscriptions', $contentRepositoryId->value), $this->dbalConnection, $clock);
    }

    private function buildContentGraphProjectionFactory(): ContentGraphProjectionFactoryInterface
    {
        return new DoctrineDbalContentGraphProjectionFactory(
            $this->dbalConnection
        );
    }

    private function buildPropertySerializer(): Serializer
    {
        $normalizers = [];

        $normalizers[] = new DateTimeNormalizer();
        $normalizers[] = new ScalarNormalizer();
        $normalizers[] = new BackedEnumNormalizer();
        $normalizers[] = new ArrayNormalizer();
        $normalizers[] = new UriNormalizer();
        $normalizers[] = new UriNormalizer();
        $normalizers[] = new ValueObjectArrayDenormalizer();
        $normalizers[] = new ValueObjectBoolDenormalizer();
        $normalizers[] = new ValueObjectFloatDenormalizer();
        $normalizers[] = new ValueObjectIntDenormalizer();
        $normalizers[] = new ValueObjectStringDenormalizerAlias();
        $normalizers[] = new CollectionTypeDenormalizer();

        return new Serializer($normalizers);
    }

    private function buildAuthProviderFactory(): \Neos\ContentRepositoryRegistry\Factory\AuthProvider\AuthProviderFactoryInterface
    {
        return new class implements \Neos\ContentRepositoryRegistry\Factory\AuthProvider\AuthProviderFactoryInterface
        {
            public function build(ContentRepositoryId $contentRepositoryId, ContentGraphReadModelInterface $contentGraphReadModel): AuthProviderInterface
            {
                return new StaticAuthProvider(UserId::forSystemUser());
            }
        };
    }

    private function buildClock(): ClockInterface
    {
        return new class implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };
    }
}
