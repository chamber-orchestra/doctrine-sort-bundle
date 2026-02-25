<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integrational;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Collector;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSetMap;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Helper\DiffHelper;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Processor;
use ChamberOrchestra\DoctrineSortBundle\Sort\RepositoryFactory;
use ChamberOrchestra\DoctrineSortBundle\Sort\Sorter;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Ds\Vector;
use Tests\Fixtures\Entity\GroupedSortableEntity;

final class SortComponentsIntegrationTest extends IntegrationTestCase
{
    public function testRepositoryQueriesAndMaxOrder(): void
    {
        $em = $this->getEntityManager();
        $em->persist(new GroupedSortableEntity(1, 'a', 1));
        $em->persist(new GroupedSortableEntity(2, 'a', 2));
        $em->persist(new GroupedSortableEntity(3, 'b', 1));
        $em->flush();

        $config = $this->getSortConfiguration($em, GroupedSortableEntity::class);
        $factory = new RepositoryFactory($em);
        $repository = $factory->getRepository($em->getClassMetadata(GroupedSortableEntity::class), $config);

        self::assertSame(3, $repository->getMaxSortOrder(['category' => 'a']));

        $collection = $repository->getCollection(['category' => 'a'], 1, 2);
        self::assertCount(2, $collection);
    }

    public function testSorterAppliesUpdateRanges(): void
    {
        $em = $this->getEntityManager();

        $first = new GroupedSortableEntity(1, 'a', 1);
        $second = new GroupedSortableEntity(2, 'a', 2);
        $em->persist($first);
        $em->persist($second);
        $em->flush();

        $config = $this->getSortConfiguration($em, GroupedSortableEntity::class);
        $changeSet = new ChangeSet($em->getClassMetadata(GroupedSortableEntity::class), $config);

        $changeSet->addDeletion($second, 2, ['category' => 'a']);
        $changeSet->addInsertion($second, 1, ['category' => 'a']);

        $sorter = new Sorter(new RepositoryFactory($em));
        $vector = $sorter->sort($changeSet);

        self::assertSame($second->getId(), $vector[0]->id);
        self::assertSame(1, $vector[0]->order);
        self::assertSame($first->getId(), $vector[1]->id);
    }

    public function testProcessorUpdatesEntities(): void
    {
        $em = $this->getEntityManager();

        $entity = new GroupedSortableEntity(1, 'a', 1);
        $em->persist($entity);
        $em->flush();

        $config = $this->getSortConfiguration($em, GroupedSortableEntity::class);
        $changeSet = new ChangeSet($em->getClassMetadata(GroupedSortableEntity::class), $config);

        $processor = new Processor();
        $processor->setCorrectOrder($em, $changeSet, new Vector([new Pair($entity->getId(), 4)]));

        self::assertSame(4, $entity->getSortOrder());
    }

    public function testCollectorAndDiffHelperProcessUpdates(): void
    {
        $em = $this->getEntityManager();

        $entity = new GroupedSortableEntity(1, 'a', 1);
        $em->persist($entity);
        $em->flush();

        $entity->setSortOrder(2);
        $entity->setCategory('b');

        $meta = $em->getClassMetadata(GroupedSortableEntity::class);
        $em->getUnitOfWork()->computeChangeSet($meta, $entity);

        $reader = self::getContainer()->get(MetadataReader::class);
        $extension = $reader->getExtensionMetadata($em, GroupedSortableEntity::class);
        $config = $extension->getConfiguration(SortConfiguration::class);

        $args = new MetadataArgs($em, $extension, $config, $entity);

        $collector = new Collector(new RepositoryFactory($em), new DiffHelper($em));
        $map = new ChangeSetMap();

        $collector->addUpdateIfNeeded($map, $args);

        self::assertNotEmpty(\iterator_to_array($map->getChangeSet($args)));
    }

    public function testSortSubscriberReordersOnUpdate(): void
    {
        $em = $this->getEntityManager();

        $first = new GroupedSortableEntity(1, 'a', 1);
        $second = new GroupedSortableEntity(2, 'a', 2);

        $em->persist($first);
        $em->persist($second);
        $em->flush();

        $second->setSortOrder(1);
        $em->flush();

        self::assertSame(2, $first->getSortOrder());
        self::assertSame(1, $second->getSortOrder());
    }

    private function getSortConfiguration($em, string $class): SortConfiguration
    {
        $reader = self::getContainer()->get(MetadataReader::class);
        $extension = $reader->getExtensionMetadata($em, $class);

        return $extension->getConfiguration(SortConfiguration::class);
    }
}
