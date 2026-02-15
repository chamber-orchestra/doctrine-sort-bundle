<?php

declare(strict_types=1);

namespace Tests\Benchmark;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Tests\Integrational\TestKernel;

abstract class SortBenchmark
{
    private static ?TestKernel $kernel = null;
    protected ?EntityManagerInterface $entityManager = null;
    private ?SchemaTool $schemaTool = null;

    public function setUp(): void
    {
        if (null === self::$kernel) {
            self::$kernel = new TestKernel('test', true);
            self::$kernel->boot();
        }

        $container = self::$kernel->getContainer()->get('test.service_container');
        $registry = $container->get(ManagerRegistry::class);

        if (!$registry->getManager()->isOpen()) {
            $registry->resetManager();
        }

        $this->entityManager = $registry->getManager();
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $this->schemaTool = new SchemaTool($this->entityManager);
        $this->schemaTool->dropSchema($metadata);
        $this->schemaTool->createSchema($metadata);
    }

    public function tearDown(): void
    {
        if (null !== $this->entityManager && null !== $this->schemaTool) {
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $this->schemaTool->dropSchema($metadata);
            $this->entityManager->clear();
        }

        $this->schemaTool = null;
        $this->entityManager = null;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
