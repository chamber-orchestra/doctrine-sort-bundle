<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Driver\SortDriver;
use ChamberOrchestra\MetadataBundle\Exception\MappingException;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use ChamberOrchestra\MetadataBundle\Reader\AttributeReader;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

#[ORM\Entity]
class SortDriverEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'integer')]
    #[Sort(groupBy: ['group'])]
    public int $sortOrder;

    #[ORM\Column(type: 'string')]
    public string $group;
}

#[ORM\Entity]
class SortDriverMissingPropertyEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'integer')]
    #[Sort(groupBy: ['missing'])]
    public int $sortOrder;
}

#[ORM\Entity]
class SortDriverMissingAnnotationEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'integer')]
    #[Sort(groupBy: ['group'])]
    public int $sortOrder;

    public string $group;
}

final class SortDriverTest extends TestCase
{
    public function testLoadMetadataCreatesSortConfiguration(): void
    {
        $metadata = $this->createMetadata(SortDriverEntity::class);
        $extension = new ExtensionMetadata($metadata);

        $driver = new SortDriver(new AttributeReader());
        $driver->loadMetadataForClass($extension);

        $config = $extension->getConfiguration(SortConfiguration::class);

        self::assertInstanceOf(SortConfiguration::class, $config);
        self::assertSame('sortOrder', $config->getSortField());
        self::assertSame(['group'], $config->getGroupingFields());
    }

    public function testMissingGroupByPropertyThrows(): void
    {
        $metadata = $this->createMetadata(SortDriverMissingPropertyEntity::class);
        $extension = new ExtensionMetadata($metadata);

        $driver = new SortDriver(new AttributeReader());

        $this->expectException(MappingException::class);

        $driver->loadMetadataForClass($extension);
    }

    public function testMissingGroupByAnnotationThrows(): void
    {
        $metadata = $this->createMetadata(SortDriverMissingAnnotationEntity::class);
        $extension = new ExtensionMetadata($metadata);

        $driver = new SortDriver(new AttributeReader());

        $this->expectException(MappingException::class);

        $driver->loadMetadataForClass($extension);
    }

    private function createMetadata(string $class): ClassMetadata
    {
        $metadata = new ClassMetadata($class);
        $metadata->initializeReflection(new RuntimeReflectionService());

        return $metadata;
    }
}
