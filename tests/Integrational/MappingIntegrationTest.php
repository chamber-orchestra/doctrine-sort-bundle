<?php

declare(strict_types=1);

namespace Tests\Integrational;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Driver\SortDriver;
use ChamberOrchestra\MetadataBundle\Mapping\MetadataReader;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use ChamberOrchestra\MetadataBundle\Reader\AttributeReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Tests\Fixtures\Entity\GroupedSortableEntity;

final class MappingIntegrationTest extends IntegrationTestCase
{
    public function testMetadataReaderBuildsSortConfiguration(): void
    {
        $em = $this->getEntityManager();
        $reader = self::getContainer()->get(MetadataReader::class);

        $extension = $reader->getExtensionMetadata($em, GroupedSortableEntity::class);
        $config = $extension->getConfiguration(SortConfiguration::class);

        self::assertInstanceOf(SortConfiguration::class, $config);
        self::assertSame('sortOrder', $config->getSortField());
        self::assertSame(['category'], $config->getGroupingFields());
    }

    public function testSortDriverLoadsConfiguration(): void
    {
        $metadata = new ClassMetadata(GroupedSortableEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());

        $driver = new SortDriver(new AttributeReader());
        $extension = new ExtensionMetadata($metadata);
        $driver->loadMetadataForClass($extension);

        self::assertInstanceOf(SortConfiguration::class, $extension->getConfiguration(SortConfiguration::class));
    }

    public function testSortAttributeIsPresent(): void
    {
        $property = new \ReflectionProperty(GroupedSortableEntity::class, 'sortOrder');
        $attributes = $property->getAttributes(Sort::class);

        self::assertCount(1, $attributes);
    }
}
