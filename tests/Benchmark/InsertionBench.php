<?php

declare(strict_types=1);

namespace Tests\Benchmark;

use PhpBench\Attributes as Bench;
use Tests\Fixtures\Entity\SimpleSortableEntity;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
#[Bench\Iterations(5)]
#[Bench\Revs(1)]
class InsertionBench extends SortBenchmark
{
    public function provideSizes(): \Generator
    {
        yield '10 entities' => ['size' => 10];
        yield '50 entities' => ['size' => 50];
        yield '100 entities' => ['size' => 100];
        yield '500 entities' => ['size' => 500];
    }

    public function benchInsertSingle(): void
    {
        $em = $this->getEntityManager();
        $em->persist(new SimpleSortableEntity(1));
        $em->flush();
    }

    #[Bench\ParamProviders('provideSizes')]
    public function benchInsertBatch(array $params): void
    {
        $em = $this->getEntityManager();

        for ($i = 1; $i <= $params['size']; ++$i) {
            $em->persist(new SimpleSortableEntity($i));
        }

        $em->flush();
    }

    #[Bench\BeforeMethods('setUpWithExistingData')]
    #[Bench\ParamProviders('provideSizes')]
    public function benchInsertAtPosition(array $params): void
    {
        $em = $this->getEntityManager();
        $em->persist(new SimpleSortableEntity($params['size'] + 1, 1));
        $em->flush();
    }

    #[Bench\BeforeMethods('setUpWithExistingData')]
    #[Bench\ParamProviders('provideSizes')]
    public function benchInsertAtMiddle(array $params): void
    {
        $em = $this->getEntityManager();
        $middle = (int) \ceil($params['size'] / 2);
        $em->persist(new SimpleSortableEntity($params['size'] + 1, $middle));
        $em->flush();
    }

    public function setUpWithExistingData(array $params): void
    {
        $this->setUp();
        $em = $this->getEntityManager();

        for ($i = 1; $i <= $params['size']; ++$i) {
            $em->persist(new SimpleSortableEntity($i));
        }

        $em->flush();
        $em->clear();
    }
}
