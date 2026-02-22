<?php

declare(strict_types=1);

namespace Tests\Benchmark;

use PhpBench\Attributes as Bench;
use Tests\Fixtures\Entity\CachedExplicitSortableEntity;

#[Bench\BeforeMethods('setUpWithExistingData')]
#[Bench\AfterMethods('tearDown')]
#[Bench\Iterations(5)]
#[Bench\Revs(1)]
class CachedReorderBench extends SortBenchmark
{
    public function provideSizes(): \Generator
    {
        yield '10 entities' => ['size' => 10];
        yield '50 entities' => ['size' => 50];
        yield '100 entities' => ['size' => 100];
        yield '500 entities' => ['size' => 500];
    }

    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideSizes')]
    public function benchMoveToFirst(array $params): void
    {
        $em = $this->getEntityManager();
        $entity = $em->find(CachedExplicitSortableEntity::class, $params['size']);
        $entity->setSortOrder(1);
        $em->persist($entity);
        $em->flush();
    }

    #[Bench\ParamProviders('provideSizes')]
    public function benchDeleteFirst(array $params): void
    {
        $em = $this->getEntityManager();
        $entity = $em->find(CachedExplicitSortableEntity::class, 1);
        $em->remove($entity);
        $em->flush();
    }

    #[Bench\ParamProviders('provideSizes')]
    public function benchDeleteMiddle(array $params): void
    {
        $em = $this->getEntityManager();
        $middle = (int) \ceil($params['size'] / 2);
        $entity = $em->find(CachedExplicitSortableEntity::class, $middle);
        $em->remove($entity);
        $em->flush();
    }

    public function setUpWithExistingData(array $params): void
    {
        $this->setUp();
        $em = $this->getEntityManager();

        for ($i = 1; $i <= $params['size']; ++$i) {
            $em->persist(new CachedExplicitSortableEntity($i));
        }

        $em->flush();
        $em->clear();
    }
}
