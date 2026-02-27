<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Benchmark;

use PhpBench\Attributes as Bench;
use Tests\Fixtures\Entity\GroupedSortableEntity;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
#[Bench\Iterations(5)]
#[Bench\Revs(1)]
class GroupedBench extends SortBenchmark
{
    private const array GROUPS = ['a', 'b', 'c', 'd', 'e'];

    public function provideSizes(): \Generator
    {
        yield '10 entities' => ['size' => 10];
        yield '50 entities' => ['size' => 50];
        yield '100 entities' => ['size' => 100];
        yield '500 entities' => ['size' => 500];
    }

    #[Bench\ParamProviders('provideSizes')]
    public function benchGroupedInsertBatch(array $params): void
    {
        $em = $this->getEntityManager();

        for ($i = 1; $i <= $params['size']; ++$i) {
            $group = self::GROUPS[$i % \count(self::GROUPS)];
            $em->persist(new GroupedSortableEntity($i, $group));
        }

        $em->flush();
    }

    #[Bench\BeforeMethods('setUpWithGroupedData')]
    #[Bench\ParamProviders('provideSizes')]
    public function benchGroupedInsertAtPosition(array $params): void
    {
        $em = $this->getEntityManager();
        $em->persist(new GroupedSortableEntity($params['size'] + 1, 'a', 1));
        $em->flush();
    }

    public function setUpWithGroupedData(array $params): void
    {
        $this->setUp();
        $em = $this->getEntityManager();

        for ($i = 1; $i <= $params['size']; ++$i) {
            $group = self::GROUPS[$i % \count(self::GROUPS)];
            $em->persist(new GroupedSortableEntity($i, $group));
        }

        $em->flush();
        $em->clear();
    }
}
