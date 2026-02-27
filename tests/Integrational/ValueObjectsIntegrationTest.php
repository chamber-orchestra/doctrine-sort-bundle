<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integrational;

use ChamberOrchestra\DoctrineSortBundle\Contracts\Entity\SortInterface;
use ChamberOrchestra\DoctrineSortBundle\Entity\SortByParentTrait;
use ChamberOrchestra\DoctrineSortBundle\Entity\SortTrait;
use ChamberOrchestra\DoctrineSortBundle\Exception\ExceptionInterface;
use ChamberOrchestra\DoctrineSortBundle\Exception\MappingException;
use ChamberOrchestra\DoctrineSortBundle\Exception\RuntimeException;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSetMap;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Range;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Update;
use ChamberOrchestra\DoctrineSortBundle\Sort\Util\Utils;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use Tests\Fixtures\Entity\GroupedSortableEntity;

final class ValueObjectsIntegrationTest extends IntegrationTestCase
{
    public function testValueObjectsBehaveWithKernel(): void
    {
        $pair = new Pair(1, 2);
        $range = new Range($pair, null);
        $update = new Update();
        $update->addInsertion($pair);

        self::assertSame(1, $pair->id);
        self::assertTrue($range->contains($pair, null));
        self::assertCount(1, $update->getRanges());
    }

    public function testChangeSetMapCreatesChangeSet(): void
    {
        $em = $this->getEntityManager();
        $entity = new GroupedSortableEntity(1, 'a', 1);

        $reader = self::getContainer()->get(\ChamberOrchestra\MetadataBundle\Mapping\MetadataReader::class);
        $extension = $reader->getExtensionMetadata($em, GroupedSortableEntity::class);
        /** @var SortConfiguration $config */
        $config = $extension->getConfiguration(SortConfiguration::class);

        $args = new MetadataArgs($em, $extension, $config, $entity);
        $map = new ChangeSetMap();

        $changeSet = $map->getChangeSet($args);
        $changeSet->addInsertion($entity, 1, ['category' => 'a']);

        self::assertNotEmpty(\iterator_to_array($changeSet));
    }

    public function testUtilsHashAndTraits(): void
    {
        $hash = Utils::hash(['alpha', 1]);

        self::assertSame(32, \strlen($hash));

        $sortTraitEntity = new class {
            use SortTrait;
        };

        $sortByParentEntity = new class {
            use SortByParentTrait;
        };

        $sortInterfaceEntity = new class implements SortInterface {
            use SortTrait;
        };
        $sortInterfaceEntity->setSortOrder(5);

        self::assertSame(0, $sortTraitEntity->getSortOrder());
        self::assertSame(0, $sortByParentEntity->getSortOrder());
        self::assertSame(5, $sortInterfaceEntity->getSortOrder());
    }

    public function testRuntimeExceptionImplementsInterface(): void
    {
        $exception = new RuntimeException('oops');

        self::assertInstanceOf(ExceptionInterface::class, $exception);
    }

    public function testMappingExceptionIsUsable(): void
    {
        $exception = new MappingException('mapping');

        self::assertSame('mapping', $exception->getMessage());
    }
}
