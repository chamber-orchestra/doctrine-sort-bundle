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
        $entity = new class () {
            use SortTrait;
        };

        self::assertSame(0, $entity->getSortOrder());
    }

    public function testSortByParentTraitDefaultOrder(): void
    {
        $entity = new class () {
            use SortByParentTrait;
        };

        self::assertSame(0, $entity->getSortOrder());
    }

    public function testSortInterfaceContract(): void
    {
        $entity = new class () implements SortInterface {
            use SortTrait;
        };

        $entity->setSortOrder(7);

        self::assertSame(7, $entity->getSortOrder());
    }

    public function testMoveUpDecrementsOrder(): void
    {
        $entity = new class () {
            use SortTrait;
        };

        $entity->setSortOrder(3);
        $entity->moveUp();

        self::assertSame(2, $entity->getSortOrder());
    }

    public function testMoveUpClampsAtOne(): void
    {
        $entity = new class () {
            use SortTrait;
        };

        $entity->setSortOrder(1);
        $entity->moveUp();

        self::assertSame(1, $entity->getSortOrder());
    }

    public function testMoveDownIncrementsOrder(): void
    {
        $entity = new class () {
            use SortTrait;
        };

        $entity->setSortOrder(2);
        $entity->moveDown();

        self::assertSame(3, $entity->getSortOrder());
    }

    public function testMoveToBeginning(): void
    {
        $entity = new class () {
            use SortTrait;
        };

        $entity->setSortOrder(5);
        $entity->moveToBeginning();

        self::assertSame(1, $entity->getSortOrder());
    }

    public function testMoveToEnd(): void
    {
        $entity = new class () {
            use SortTrait;
        };

        $entity->setSortOrder(3);
        $entity->moveToEnd();

        self::assertSame(0, $entity->getSortOrder());
    }

    public function testSortByParentTraitInheritsMethods(): void
    {
        $entity = new class () {
            use SortByParentTrait;
        };

        $entity->setSortOrder(5);
        $entity->moveUp();

        self::assertSame(4, $entity->getSortOrder());

        $entity->moveToEnd();

        self::assertSame(0, $entity->getSortOrder());
    }
}
