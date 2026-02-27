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
}
