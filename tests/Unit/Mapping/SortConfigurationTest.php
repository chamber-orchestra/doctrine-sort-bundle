<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use PHPUnit\Framework\TestCase;

final class SortConfigurationTest extends TestCase
{
    public function testAccessorsReturnMappingData(): void
    {
        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => ['category'],
            'evictCollections' => [['Foo', 'bar']],
            'evictRegions' => ['region_a'],
            'entityName' => 'Tests\\Fixtures\\Entity\\GroupedSortableEntity',
        ]);

        self::assertSame('sortOrder', $config->getSortField());
        self::assertSame(['category'], $config->getGroupingFields());
        self::assertSame([['Foo', 'bar']], $config->getEvictCacheCollections());
        self::assertSame(['region_a'], $config->getEvictCacheRegions());
        self::assertSame('Tests\\Fixtures\\Entity\\GroupedSortableEntity', $config->getEntityName());
    }
}
