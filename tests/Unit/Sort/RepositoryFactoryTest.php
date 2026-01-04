<?php

declare(strict_types=1);

namespace Tests\Unit\Sort;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\RepositoryFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class RepositoryFactoryEntity
{
    public int $id = 1;
}

final class RepositoryFactoryTest extends TestCase
{
    public function testGetRepositoryCachesInstances(): void
    {
        $metadata = new ClassMetadata(RepositoryFactoryEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => RepositoryFactoryEntity::class,
        ]);

        $em = $this->createStub(EntityManagerInterface::class);
        $factory = new RepositoryFactory($em);

        $first = $factory->getRepository($metadata, $config);
        $second = $factory->getRepository($metadata, $config);

        self::assertSame($first, $second);
    }
}
