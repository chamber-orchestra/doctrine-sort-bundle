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
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Update;
use PHPUnit\Framework\TestCase;

final class UpdateTest extends TestCase
{
    public function testGetRangesBuildsSingleRange(): void
    {
        $update = new Update(['category' => 'a']);
        $update->addInsertion(new Pair(1, 3));
        $update->addDeletion(new Pair(2, 5));

        $ranges = $update->getRanges();

        self::assertCount(1, $ranges);
        self::assertSame(3, $ranges[0]->getMin());
        self::assertSame(5, $ranges[0]->getMax());
    }

    public function testGetRangesHandlesMismatchedCounts(): void
    {
        $update = new Update();
        $update->addInsertion(new Pair(1, 2));
        $update->addInsertion(new Pair(2, 4));
        $update->addDeletion(new Pair(3, 3));

        $ranges = $update->getRanges();

        self::assertGreaterThanOrEqual(1, \count($ranges));
    }
}
