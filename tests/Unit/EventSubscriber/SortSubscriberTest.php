<?php

declare(strict_types=1);

namespace Tests\Unit\EventSubscriber;

use ChamberOrchestra\DoctrineSortBundle\EventSubscriber\SortSubscriber;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSetMap;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class SortSubscriberEntity
{
    public int $id = 1;
    public int $sortOrder = 1;
}

final class SortSubscriberTest extends TestCase
{
    public function testPostFlushResetsCaches(): void
    {
        $subscriber = new SortSubscriber();

        $this->setPrivateProperty($subscriber, 'collectors', ['foo' => 'bar']);
        $this->setPrivateProperty($subscriber, 'changeSetMaps', ['foo' => 'bar']);
        $this->setPrivateProperty($subscriber, 'sorters', ['foo' => 'bar']);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getCache')->willReturn(null);

        $subscriber->postFlush(new PostFlushEventArgs($em));

        self::assertSame([], $this->getPrivateProperty($subscriber, 'collectors'));
        self::assertSame([], $this->getPrivateProperty($subscriber, 'changeSetMaps'));
        self::assertSame([], $this->getPrivateProperty($subscriber, 'sorters'));
    }

    public function testPostFlushEvictsConfiguredCacheRegions(): void
    {
        $metadata = new ClassMetadata(SortSubscriberEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->mapField(['fieldName' => 'sortOrder', 'type' => 'integer']);
        $metadata->identifier = ['id'];
        $metadata->wakeupReflection(new RuntimeReflectionService());

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [[SortSubscriberEntity::class, 'items']],
            'evictRegions' => ['region_a'],
            'entityName' => SortSubscriberEntity::class,
        ]);

        $cache = $this->createMock(Cache::class);
        $cache->expects(self::once())
            ->method('evictCollectionRegion')
            ->with(SortSubscriberEntity::class, 'items');
        $cache->expects(self::once())
            ->method('evictQueryRegion')
            ->with('region_a');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getCache')->willReturn($cache);
        $em->method('getClassMetadata')->with(SortSubscriberEntity::class)->willReturn($metadata);

        $extension = $this->createStub(ExtensionMetadataInterface::class);
        $args = new MetadataArgs($em, $extension, $config, new SortSubscriberEntity());

        $map = new ChangeSetMap();
        $map->getChangeSet($args);

        $subscriber = new SortSubscriber();
        $this->setPrivateProperty($subscriber, 'changeSetMaps', [\get_class($em) => $map]);

        $subscriber->postFlush(new PostFlushEventArgs($em));

    }

    private function setPrivateProperty(object $subject, string $property, mixed $value): void
    {
        $setter = function (string $property, mixed $value): void {
            $this->{$property} = $value;
        };

        $setter->bindTo($subject, $subject::class)($property, $value);
    }

    private function getPrivateProperty(object $subject, string $property): mixed
    {
        $getter = function (string $property): mixed {
            return $this->{$property};
        };

        return $getter->bindTo($subject, $subject::class)($property);
    }
}
