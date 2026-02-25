<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Sort\Orm;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSetMap;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class ChangeSetMapEntity
{
    public int $id = 1;
}

final class ChangeSetMapTest extends TestCase
{
    public function testGetChangeSetCachesByClass(): void
    {
        $metadata = new ClassMetadata(ChangeSetMapEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];
        $metadata->wakeupReflection(new RuntimeReflectionService());

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => ChangeSetMapEntity::class,
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getClassMetadata')->with(ChangeSetMapEntity::class)->willReturn($metadata);

        $extension = $this->createStub(ExtensionMetadataInterface::class);

        $args = new MetadataArgs($em, $extension, $config, new ChangeSetMapEntity());

        $map = new ChangeSetMap();

        $first = $map->getChangeSet($args);
        $second = $map->getChangeSet($args);

        self::assertSame($first, $second);
    }
}
