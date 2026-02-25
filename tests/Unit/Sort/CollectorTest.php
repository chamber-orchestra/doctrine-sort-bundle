<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Sort;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Collector;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSetMap;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Helper\DiffHelper;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Update;
use ChamberOrchestra\DoctrineSortBundle\Sort\Repository\EntityRepository;
use ChamberOrchestra\DoctrineSortBundle\Sort\RepositoryFactory;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class CollectorEntity
{
    public int $id = 1;
    public int $sortOrder = 1;
}

final class CollectorTest extends TestCase
{
    public function testAddUpdateIfNeededCreatesChangeSet(): void
    {
        $metadata = new ClassMetadata(CollectorEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];
        $metadata->wakeupReflection(new RuntimeReflectionService());

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => CollectorEntity::class,
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getClassMetadata')->with(CollectorEntity::class)->willReturn($metadata);

        $extension = $this->createStub(ExtensionMetadataInterface::class);
        $args = new MetadataArgs($em, $extension, $config, new CollectorEntity());

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('getMaxSortOrder')->willReturn(1);

        $factory = $this->createStub(RepositoryFactory::class);
        $factory->method('getRepository')->willReturn($repo);

        $helper = $this->createStub(DiffHelper::class);
        $helper->method('hasChangedFields')->willReturn(true);
        $helper->method('getGroupingFieldChangeSet')->willReturn([[], []]);
        $helper->method('getSortFieldChangeSet')->willReturn([1, 2]);

        $collector = new Collector($factory, $helper);
        $map = new ChangeSetMap();

        $collector->addUpdateIfNeeded($map, $args);

        $updates = \iterator_to_array($map->getChangeSet($args));

        self::assertCount(1, $updates);
        self::assertInstanceOf(Update::class, \array_values($updates)[0]);
    }
}
