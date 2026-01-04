<?php

declare(strict_types=1);

namespace Tests\Unit\Sort\Orm;

use ChamberOrchestra\DoctrineSortBundle\Exception\RuntimeException;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Range;
use PHPUnit\Framework\TestCase;

final class RangeTest extends TestCase
{
    public function testRangeTracksBounds(): void
    {
        $range = new Range(new Pair(1, 5), new Pair(2, 2));

        self::assertSame(2, $range->getMin());
        self::assertSame(5, $range->getMax());
    }

    public function testContainsOverlappingRange(): void
    {
        $range = new Range(new Pair(1, 5), new Pair(2, 2));

        self::assertTrue($range->contains(new Pair(3, 4), null));
    }

    public function testContainsAdjacentRange(): void
    {
        $range = new Range(new Pair(1, 5), new Pair(2, 7));

        self::assertTrue($range->contains(new Pair(3, 8), null));
        self::assertFalse($range->contains(new Pair(4, 10), null));
    }

    public function testInsertionsAndDeletionsAreSorted(): void
    {
        $range = new Range(new Pair(1, 3), new Pair(2, 5));
        $range->add(new Pair(3, 1), new Pair(4, 10));

        $insertions = $range->getInsertions();
        $deletions = $range->getDeletions();

        self::assertSame([1, 3], [$insertions[0]->order, $insertions[1]->order]);
        self::assertSame([10, 5], [$deletions[0]->order, $deletions[1]->order]);
    }

    public function testNullInsertionsAndDeletionsThrow(): void
    {
        $this->expectException(RuntimeException::class);

        new Range(null, null);
    }
}
