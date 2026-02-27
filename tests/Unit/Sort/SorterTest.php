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
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Repository\EntityRepository;
use ChamberOrchestra\DoctrineSortBundle\Sort\RepositoryFactory;
use ChamberOrchestra\DoctrineSortBundle\Sort\Sorter;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Ds\Vector;
use PHPUnit\Framework\TestCase;

class SorterEntity
{
    public int $id;
    public int $sortOrder = 1;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}

final class SorterTest extends TestCase
{
    public function testSortAppliesInsertionsAndDeletions(): void
    {
        $metadata = new ClassMetadata(SorterEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];
        $metadata->wakeupReflection(new RuntimeReflectionService());

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => SorterEntity::class,
        ]);

        $changeSet = new ChangeSet($metadata, $config);

        $changeSet->addDeletion(new SorterEntity(1), 1, []);
        $changeSet->addInsertion(new SorterEntity(3), 1, []);

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('getCollection')->willReturn(new Vector([
            new Pair(1, 1),
            new Pair(2, 2),
        ]));

        $factory = $this->createStub(RepositoryFactory::class);
        $factory->method('getRepository')->willReturn($repo);

        $sorter = new Sorter($factory);
        $vector = $sorter->sort($changeSet);

        self::assertSame([3, 2], [$vector[0]->id, $vector[1]->id]);
        self::assertSame([1, 2], [$vector[0]->order, $vector[1]->order]);
    }
}
