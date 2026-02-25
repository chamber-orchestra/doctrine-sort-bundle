<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function testGroupByRejectsNonString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each "groupBy" element must be a string.');

        new Sort(groupBy: [123]);
    }

    public function testEvictCollectionsRejectsNonArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each "evictCollections" element must be a 2-element array of [string, string].');

        new Sort(evictCollections: ['not-an-array']);
    }

    public function testEvictCollectionsRejectsWrongLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each "evictCollections" element must be a 2-element array of [string, string].');

        new Sort(evictCollections: [['only-one']]);
    }

    public function testEvictCollectionsRejectsNonStringElements(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each "evictCollections" element must be a 2-element array of [string, string].');

        new Sort(evictCollections: [[123, 'bar']]);
    }

    public function testEvictRegionsRejectsNonString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each "evictRegions" element must be a string.');

        new Sort(evictRegions: [42]);
    }

    public function testDefaultsAreValid(): void
    {
        $attribute = new Sort();

        self::assertSame([], $attribute->groupBy);
        self::assertSame([], $attribute->evictCollections);
        self::assertSame([], $attribute->evictRegions);
    }
}
