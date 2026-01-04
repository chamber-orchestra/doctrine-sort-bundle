<?php

declare(strict_types=1);

namespace Tests\Unit\Mapping;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use PHPUnit\Framework\TestCase;

final class SortAttributeTest extends TestCase
{
    public function testConstructSetsProperties(): void
    {
        $attribute = new Sort(groupBy: ['category'], evictCollections: [['Foo', 'bar']], evictRegions: ['baz']);

        self::assertSame(['category'], $attribute->groupBy);
        self::assertSame([['Foo', 'bar']], $attribute->evictCollections);
        self::assertSame(['baz'], $attribute->evictRegions);
    }
}
