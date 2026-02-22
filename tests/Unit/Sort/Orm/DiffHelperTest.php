<?php

declare(strict_types=1);

namespace Tests\Unit\Sort\Orm;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Helper\DiffHelper;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class DiffHelperEntity
{
    public int $id = 1;
    public int $sortOrder = 1;
    public string $group = 'a';
}

final class DiffHelperTest extends TestCase
{
    public function testChangeSetExtraction(): void
    {
        $metadata = new ClassMetadata(DiffHelperEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->mapField(['fieldName' => 'sortOrder', 'type' => 'integer']);
        $metadata->mapField(['fieldName' => 'group', 'type' => 'string']);
        $metadata->identifier = ['id'];
        $metadata->wakeupReflection(new RuntimeReflectionService());

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => ['group'],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => DiffHelperEntity::class,
        ]);

        $uow = $this->createStub(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([
            'sortOrder' => [1, 2],
            'group' => ['a', 'b'],
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getUnitOfWork')->willReturn($uow);
        $em->method('getClassMetadata')->with(DiffHelperEntity::class)->willReturn($metadata);

        $extension = $this->createStub(ExtensionMetadataInterface::class);
        $args = new MetadataArgs($em, $extension, $config, new DiffHelperEntity());

        $helper = new DiffHelper($em);

        self::assertSame([1, 2], $helper->getSortFieldChangeSet($args));
        self::assertSame([['group' => 'a'], ['group' => 'b']], $helper->getGroupingFieldChangeSet($args));
        self::assertTrue($helper->hasChangedFields($args));
    }
}
