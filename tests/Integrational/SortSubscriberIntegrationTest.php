<?php

declare(strict_types=1);

namespace Tests\Integrational;

use Tests\Fixtures\Entity\CachedExplicitSortableEntity;
use Tests\Fixtures\Entity\GroupedSortableEntity;
use Tests\Fixtures\Entity\SimpleSortableEntity;

final class SortSubscriberIntegrationTest extends IntegrationTestCase
{
    // ── Ungrouped (SimpleSortableEntity) ────────────────────────────

    public function testInsertSingleEntityAssignsOrder(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->flush();

        $entity = $em->find(SimpleSortableEntity::class, 1);
        self::assertSame(1, $entity->getSortOrder());
    }

    public function testInsertMultipleEntitiesAssignsSequentialOrder(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testInsertAtSpecificPosition(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->flush();

        $em->persist(new SimpleSortableEntity(3, 1));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
    }

    public function testInsertAtMiddlePosition(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        $em->persist(new SimpleSortableEntity(4, 2));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testMoveEntityUp(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        $entity = $em->find(SimpleSortableEntity::class, 3);
        $entity->setSortOrder(1);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
    }

    public function testMoveEntityDown(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        $entity = $em->find(SimpleSortableEntity::class, 1);
        $entity->setSortOrder(3);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
    }

    public function testDeleteFirstEntityReordersRemaining(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        $em->remove($em->find(SimpleSortableEntity::class, 1));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testDeleteMiddleEntityReordersRemaining(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        $em->remove($em->find(SimpleSortableEntity::class, 2));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testDeleteLastEntityKeepsOrder(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        $em->remove($em->find(SimpleSortableEntity::class, 3));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
    }

    public function testInsertWithZeroOrderAppendsToEnd(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->flush();

        $em->persist(new SimpleSortableEntity(3, 0));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testSwapTwoEntitiesInSingleFlush(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->flush();

        // Swap first and last in one flush
        $em->find(SimpleSortableEntity::class, 1)->setSortOrder(4);
        $em->find(SimpleSortableEntity::class, 4)->setSortOrder(1);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
    }

    public function testMoveMultipleEntitiesUpInSingleFlush(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->flush();

        // Move entities 4 and 5 to the top
        $em->find(SimpleSortableEntity::class, 4)->setSortOrder(1);
        $em->find(SimpleSortableEntity::class, 5)->setSortOrder(2);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(5, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testMoveMultipleEntitiesDownInSingleFlush(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->flush();

        // Move entities 1 and 2 to the bottom
        $em->find(SimpleSortableEntity::class, 1)->setSortOrder(4);
        $em->find(SimpleSortableEntity::class, 2)->setSortOrder(5);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(5, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
    }

    public function testMoveOneUpAndOneDownInSingleFlush(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->flush();

        // Move entity 1 down and entity 5 up simultaneously
        $em->find(SimpleSortableEntity::class, 1)->setSortOrder(4);
        $em->find(SimpleSortableEntity::class, 5)->setSortOrder(2);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(5, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
    }

    public function testMoveWithInsertAndDeleteInSingleFlush(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->flush();

        // Delete entity 2, move entity 4 up, insert new entity — all in one flush
        $em->remove($em->find(SimpleSortableEntity::class, 2));
        $em->find(SimpleSortableEntity::class, 4)->setSortOrder(1);
        $em->persist(new SimpleSortableEntity(5));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
    }

    public function testMoveUpThenDownAcrossTwoFlushes(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->flush();

        // First flush: move entity 5 to position 1
        $em->find(SimpleSortableEntity::class, 5)->setSortOrder(1);
        $em->flush();

        // Second flush (same transaction, no clear): move entity 1 to position 5
        $em->find(SimpleSortableEntity::class, 1)->setSortOrder(5);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(5, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
    }

    public function testMultipleMovesAcrossTwoFlushes(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->persist(new SimpleSortableEntity(6));
        $em->flush();

        // First flush: move entities 5 and 6 to the top
        $em->find(SimpleSortableEntity::class, 5)->setSortOrder(1);
        $em->find(SimpleSortableEntity::class, 6)->setSortOrder(2);
        $em->flush();

        // After first flush: [5,6,1,2,3,4]
        // Second flush: move entities 1 and 2 to the bottom
        $em->find(SimpleSortableEntity::class, 1)->setSortOrder(5);
        $em->find(SimpleSortableEntity::class, 2)->setSortOrder(6);
        $em->flush();

        $em->clear();

        // Final: [5,6,3,4,1,2]
        self::assertSame(1, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 6)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(5, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(6, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
    }

    public function testInsertAndReorderAcrossTwoFlushes(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        // First flush: insert new entity at position 1
        $em->persist(new SimpleSortableEntity(4, 1));
        $em->flush();

        // After first flush: [4,1,2,3]
        // Second flush: move entity 3 to position 2 and entity 4 to position 3
        $em->find(SimpleSortableEntity::class, 3)->setSortOrder(2);
        $em->find(SimpleSortableEntity::class, 4)->setSortOrder(3);
        $em->flush();

        $em->clear();

        // Final: [1,3,4,2]
        self::assertSame(1, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
    }

    public function testDeleteAndReorderAcrossTwoFlushes(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->flush();

        // First flush: delete entity 1 and move entity 5 to position 1
        $em->remove($em->find(SimpleSortableEntity::class, 1));
        $em->find(SimpleSortableEntity::class, 5)->setSortOrder(1);
        $em->flush();

        // After first flush: [5,2,3,4]
        // Second flush: delete entity 2 and move entity 4 to position 1
        $em->remove($em->find(SimpleSortableEntity::class, 2));
        $em->find(SimpleSortableEntity::class, 4)->setSortOrder(1);
        $em->flush();

        $em->clear();

        // Final: [4,5,3]
        self::assertSame(1, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testReverseOrderAcrossTwoFlushes(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->flush();

        // First flush: move entity 4 to 1 and entity 3 to 2
        $em->find(SimpleSortableEntity::class, 4)->setSortOrder(1);
        $em->find(SimpleSortableEntity::class, 3)->setSortOrder(2);
        $em->flush();

        // After first flush: [4,3,1,2]
        // Second flush: move entity 2 to 3 and entity 1 to 4
        $em->find(SimpleSortableEntity::class, 2)->setSortOrder(3);
        $em->find(SimpleSortableEntity::class, 1)->setSortOrder(4);
        $em->flush();

        $em->clear();

        // Final: [4,3,2,1]
        self::assertSame(1, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
    }

    // ── Explicit transactions ────────────────────────────────────────

    public function testMoveUpAndDownInExplicitTransaction(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->flush();

        $em->getConnection()->beginTransaction();

        try {
            // First flush: move entity 5 to top
            $em->find(SimpleSortableEntity::class, 5)->setSortOrder(1);
            $em->flush();

            // Second flush: move entity 1 to bottom
            $em->find(SimpleSortableEntity::class, 1)->setSortOrder(5);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Throwable $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }

        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(5, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
    }

    public function testMultipleMovesInExplicitTransaction(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->persist(new SimpleSortableEntity(5));
        $em->persist(new SimpleSortableEntity(6));
        $em->flush();

        $em->getConnection()->beginTransaction();

        try {
            // First flush: move bottom two to top
            $em->find(SimpleSortableEntity::class, 5)->setSortOrder(1);
            $em->find(SimpleSortableEntity::class, 6)->setSortOrder(2);
            $em->flush();

            // After first flush: [5,6,1,2,3,4]
            // Second flush: move original top two to bottom
            $em->find(SimpleSortableEntity::class, 1)->setSortOrder(5);
            $em->find(SimpleSortableEntity::class, 2)->setSortOrder(6);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Throwable $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }

        $em->clear();

        // Final: [5,6,3,4,1,2]
        self::assertSame(1, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 6)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(5, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(6, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
    }

    public function testInsertDeleteAndReorderInExplicitTransaction(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->persist(new SimpleSortableEntity(4));
        $em->flush();

        $em->getConnection()->beginTransaction();

        try {
            // First flush: delete entity 1, insert new entity at top
            $em->remove($em->find(SimpleSortableEntity::class, 1));
            $em->persist(new SimpleSortableEntity(5, 1));
            $em->flush();

            // After first flush: [5,2,3,4]
            // Second flush: move entity 4 up and entity 5 down
            $em->find(SimpleSortableEntity::class, 4)->setSortOrder(1);
            $em->find(SimpleSortableEntity::class, 5)->setSortOrder(3);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Throwable $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }

        $em->clear();

        // Final: [4,2,5,3]
        self::assertSame(1, $em->find(SimpleSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 5)->getSortOrder());
        self::assertSame(4, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    public function testRollbackRevertsAllSortChanges(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new SimpleSortableEntity(1));
        $em->persist(new SimpleSortableEntity(2));
        $em->persist(new SimpleSortableEntity(3));
        $em->flush();

        $em->getConnection()->beginTransaction();

        // Move entity 3 to top inside the transaction
        $em->find(SimpleSortableEntity::class, 3)->setSortOrder(1);
        $em->flush();

        // Rollback — changes should be reverted in the database
        $em->getConnection()->rollBack();
        $em->clear();

        self::assertSame(1, $em->find(SimpleSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(SimpleSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(SimpleSortableEntity::class, 3)->getSortOrder());
    }

    // ── Grouped (GroupedSortableEntity) ─────────────────────────────

    public function testGroupedInsertIsolatedPerGroup(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->persist(new GroupedSortableEntity(2, 'a'));
        $em->persist(new GroupedSortableEntity(3, 'b'));
        $em->persist(new GroupedSortableEntity(4, 'b'));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(GroupedSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 2)->getSortOrder());
        self::assertSame(1, $em->find(GroupedSortableEntity::class, 3)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 4)->getSortOrder());
    }

    public function testGroupedInsertAtPositionAffectsOnlyGroup(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->persist(new GroupedSortableEntity(2, 'a'));
        $em->persist(new GroupedSortableEntity(3, 'b'));
        $em->flush();

        $em->persist(new GroupedSortableEntity(4, 'a', 1));
        $em->flush();

        $em->clear();

        // Group 'a': new entity at 1, existing shift down
        self::assertSame(1, $em->find(GroupedSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 1)->getSortOrder());
        self::assertSame(3, $em->find(GroupedSortableEntity::class, 2)->getSortOrder());

        // Group 'b': unaffected
        self::assertSame(1, $em->find(GroupedSortableEntity::class, 3)->getSortOrder());
    }

    public function testGroupedMoveWithinGroup(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->persist(new GroupedSortableEntity(2, 'a'));
        $em->persist(new GroupedSortableEntity(3, 'a'));
        $em->flush();

        $entity = $em->find(GroupedSortableEntity::class, 3);
        $entity->setSortOrder(1);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(GroupedSortableEntity::class, 3)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 1)->getSortOrder());
        self::assertSame(3, $em->find(GroupedSortableEntity::class, 2)->getSortOrder());
    }

    public function testGroupedMoveBetweenGroups(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->persist(new GroupedSortableEntity(2, 'a'));
        $em->persist(new GroupedSortableEntity(3, 'b'));
        $em->persist(new GroupedSortableEntity(4, 'b'));
        $em->flush();

        // Move entity 2 from group 'a' to group 'b' at position 1
        $entity = $em->find(GroupedSortableEntity::class, 2);
        $entity->setCategory('b');
        $entity->setSortOrder(1);
        $em->flush();

        $em->clear();

        // Group 'a': entity 1 remains, reordered
        self::assertSame(1, $em->find(GroupedSortableEntity::class, 1)->getSortOrder());
        self::assertSame('a', $em->find(GroupedSortableEntity::class, 1)->getCategory());

        // Group 'b': entity 2 inserted at 1, others shift
        self::assertSame(1, $em->find(GroupedSortableEntity::class, 2)->getSortOrder());
        self::assertSame('b', $em->find(GroupedSortableEntity::class, 2)->getCategory());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 3)->getSortOrder());
        self::assertSame(3, $em->find(GroupedSortableEntity::class, 4)->getSortOrder());
    }

    public function testGroupedDeleteReordersOnlyAffectedGroup(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->persist(new GroupedSortableEntity(2, 'a'));
        $em->persist(new GroupedSortableEntity(3, 'a'));
        $em->persist(new GroupedSortableEntity(4, 'b'));
        $em->persist(new GroupedSortableEntity(5, 'b'));
        $em->flush();

        $em->remove($em->find(GroupedSortableEntity::class, 1));
        $em->flush();

        $em->clear();

        // Group 'a': reordered after deletion
        self::assertSame(1, $em->find(GroupedSortableEntity::class, 2)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 3)->getSortOrder());

        // Group 'b': unaffected
        self::assertSame(1, $em->find(GroupedSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 5)->getSortOrder());
    }

    public function testGroupedMultipleOperationsInSingleFlush(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->persist(new GroupedSortableEntity(2, 'a'));
        $em->persist(new GroupedSortableEntity(3, 'a'));
        $em->flush();

        // Delete first and insert new at position 1 in same flush
        $em->remove($em->find(GroupedSortableEntity::class, 1));
        $em->persist(new GroupedSortableEntity(4, 'a', 1));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(GroupedSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(GroupedSortableEntity::class, 3)->getSortOrder());
    }

    public function testGroupedAppendToEmptyGroup(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(GroupedSortableEntity::class, 1)->getSortOrder());
    }

    public function testGroupedInsertWithOutOfRangeOrderClampsToEnd(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new GroupedSortableEntity(1, 'a'));
        $em->persist(new GroupedSortableEntity(2, 'a'));
        $em->flush();

        // Sort order 999 should be clamped to maxOrder+1 = 3
        $em->persist(new GroupedSortableEntity(3, 'a', 999));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(GroupedSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(GroupedSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(GroupedSortableEntity::class, 3)->getSortOrder());
    }

    // ── Cached + DEFERRED_EXPLICIT (CachedExplicitSortableEntity) ───

    public function testCachedExplicitInsertAssignsOrder(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(CachedExplicitSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 3)->getSortOrder());
    }

    public function testCachedExplicitMoveUpRequiresPersist(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->flush();

        // With DEFERRED_EXPLICIT, must call persist() to schedule dirty check
        $entity = $em->find(CachedExplicitSortableEntity::class, 3);
        $entity->setSortOrder(1);
        $em->persist($entity);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(CachedExplicitSortableEntity::class, 3)->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 1)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
    }

    public function testCachedExplicitMoveDownRequiresPersist(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->flush();

        $entity = $em->find(CachedExplicitSortableEntity::class, 1);
        $entity->setSortOrder(3);
        $em->persist($entity);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 3)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 1)->getSortOrder());
    }

    public function testCachedExplicitSwapInSingleFlush(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->persist(new CachedExplicitSortableEntity(4));
        $em->flush();

        $first = $em->find(CachedExplicitSortableEntity::class, 1);
        $first->setSortOrder(4);
        $em->persist($first);

        $last = $em->find(CachedExplicitSortableEntity::class, 4);
        $last->setSortOrder(1);
        $em->persist($last);

        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(CachedExplicitSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(CachedExplicitSortableEntity::class, 1)->getSortOrder());
    }

    public function testCachedExplicitMultipleMovesAcrossTwoFlushes(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->persist(new CachedExplicitSortableEntity(4));
        $em->persist(new CachedExplicitSortableEntity(5));
        $em->flush();

        // First flush: move entity 5 to top
        $entity5 = $em->find(CachedExplicitSortableEntity::class, 5);
        $entity5->setSortOrder(1);
        $em->persist($entity5);
        $em->flush();

        // Second flush: move entity 1 to bottom
        $entity1 = $em->find(CachedExplicitSortableEntity::class, 1);
        $entity1->setSortOrder(5);
        $em->persist($entity1);
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(CachedExplicitSortableEntity::class, 5)->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 3)->getSortOrder());
        self::assertSame(4, $em->find(CachedExplicitSortableEntity::class, 4)->getSortOrder());
        self::assertSame(5, $em->find(CachedExplicitSortableEntity::class, 1)->getSortOrder());
    }

    public function testCachedExplicitDeleteAndReorder(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->persist(new CachedExplicitSortableEntity(4));
        $em->flush();

        $em->remove($em->find(CachedExplicitSortableEntity::class, 1));

        $entity4 = $em->find(CachedExplicitSortableEntity::class, 4);
        $entity4->setSortOrder(1);
        $em->persist($entity4);

        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(CachedExplicitSortableEntity::class, 4)->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 3)->getSortOrder());
    }

    public function testCachedExplicitInsertAtPositionWithExistingData(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->flush();

        // Insert new entity at position 2
        $em->persist(new CachedExplicitSortableEntity(4, 2));
        $em->flush();

        $em->clear();

        self::assertSame(1, $em->find(CachedExplicitSortableEntity::class, 1)->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 4)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
        self::assertSame(4, $em->find(CachedExplicitSortableEntity::class, 3)->getSortOrder());
    }

    public function testCachedExplicitCacheIsConsistentAfterReorder(): void
    {
        $em = $this->getEntityManager();

        $em->persist(new CachedExplicitSortableEntity(1));
        $em->persist(new CachedExplicitSortableEntity(2));
        $em->persist(new CachedExplicitSortableEntity(3));
        $em->flush();

        $entity = $em->find(CachedExplicitSortableEntity::class, 3);
        $entity->setSortOrder(1);
        $em->persist($entity);
        $em->flush();

        // Clear identity map to force loading from SLC or database
        $em->clear();

        // First read — populates SLC
        $e1 = $em->find(CachedExplicitSortableEntity::class, 3);
        self::assertSame(1, $e1->getSortOrder());

        // Clear again — next read should come from SLC
        $em->clear();

        $e2 = $em->find(CachedExplicitSortableEntity::class, 3);
        self::assertSame(1, $e2->getSortOrder());
        self::assertSame(2, $em->find(CachedExplicitSortableEntity::class, 1)->getSortOrder());
        self::assertSame(3, $em->find(CachedExplicitSortableEntity::class, 2)->getSortOrder());
    }
}
