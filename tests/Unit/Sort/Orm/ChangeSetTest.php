<?php

declare(strict_types=1);

namespace Tests\Unit\Sort\Orm;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Update;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class ChangeSetEntity
{
    public int $id = 1;
    public int $sortOrder = 1;
}

final class ChangeSetTest extends TestCase
{
    public function testAddInsertionAndDeletionCreatesUpdate(): void
    {
        $metadata = new ClassMetadata(ChangeSetEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];
        $metadata->wakeupReflection(new RuntimeReflectionService());

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => ChangeSetEntity::class,
        ]);

        $changeSet = new ChangeSet($metadata, $config);
        $entity = new ChangeSetEntity();

        $changeSet->addInsertion($entity, 1, ['group' => 'a']);
        $changeSet->addDeletion($entity, 2, ['group' => 'a']);

        $updates = \iterator_to_array($changeSet);

        self::assertCount(1, $updates);
        self::assertInstanceOf(Update::class, \array_values($updates)[0]);
    }
}
