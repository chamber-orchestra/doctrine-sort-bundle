<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Sort\Orm;

use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use PHPUnit\Framework\TestCase;

final class PairTest extends TestCase
{
    public function testPairStoresIdAndOrder(): void
    {
        $pair = new Pair(10, 2);

        self::assertSame(10, $pair->id);
        self::assertSame(2, $pair->order);
    }

    public function testPairAcceptsStringId(): void
    {
        $pair = new Pair('018f9d4a-0f1c-7c3a-9f1a-1234567890ab', 5);

        self::assertSame('018f9d4a-0f1c-7c3a-9f1a-1234567890ab', $pair->id);
        self::assertSame(5, $pair->order);
    }
}
