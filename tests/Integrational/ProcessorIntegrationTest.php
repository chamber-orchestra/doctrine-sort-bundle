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
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Processor;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use Ds\Vector;
use Tests\Fixtures\Entity\GroupedSortableEntity;

final class ProcessorIntegrationTest extends IntegrationTestCase
{
    public function testProcessorThrowsWhenEntityMissing(): void
    {
        $em = $this->getEntityManager();
        $entity = new GroupedSortableEntity(1, 'a', 1);
        $em->persist($entity);
        $em->flush();

        $config = $this->getSortConfiguration($em, GroupedSortableEntity::class);
        $changeSet = new ChangeSet($em->getClassMetadata(GroupedSortableEntity::class), $config);

        $processor = new Processor();

        $this->expectException(\RuntimeException::class);

        $processor->setCorrectOrder($em, $changeSet, new Vector([new Pair(999, 1)]));
    }

    private function getSortConfiguration($em, string $class): SortConfiguration
    {
        $reader = self::getContainer()->get(MetadataReader::class);
        $extension = $reader->getExtensionMetadata($em, $class);

        return $extension->getConfiguration(SortConfiguration::class);
    }
}
