<?php

declare(strict_types=1);

namespace Tests\Unit\Entity;

use ChamberOrchestra\DoctrineSortBundle\Contracts\Entity\SortInterface;
use ChamberOrchestra\DoctrineSortBundle\Entity\SortByParentTrait;
use ChamberOrchestra\DoctrineSortBundle\Entity\SortTrait;
use PHPUnit\Framework\TestCase;

final class SortTraitTest extends TestCase
{
    public function testSortTraitDefaultOrder(): void
    {
        $entity = new class {
            use SortTrait;
        };

        self::assertSame(PHP_INT_MAX, $entity->getSortOrder());
    }

    public function testSortByParentTraitDefaultOrder(): void
    {
        $entity = new class {
            use SortByParentTrait;
        };

        self::assertSame(PHP_INT_MAX, $entity->getSortOrder());
    }

    public function testSortInterfaceContract(): void
    {
        $entity = new class implements SortInterface {
            public function getSortOrder(): int
            {
                return 7;
            }
        };

        self::assertSame(7, $entity->getSortOrder());
    }
}
