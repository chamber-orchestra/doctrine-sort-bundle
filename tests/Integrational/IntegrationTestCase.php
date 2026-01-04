<?php

declare(strict_types=1);

namespace Tests\Integrational;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTestCase extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?SchemaTool $schemaTool = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if (null !== $this->entityManager) {
            return $this->entityManager;
        }

        $registry = self::getContainer()->get(ManagerRegistry::class);
        $this->entityManager = $registry->getManager();

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $this->schemaTool = new SchemaTool($this->entityManager);
        $this->schemaTool->createSchema($metadata);

        return $this->entityManager;
    }

    protected function tearDown(): void
    {
        if (null !== $this->entityManager && null !== $this->schemaTool) {
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $this->schemaTool->dropSchema($metadata);
            $this->entityManager->clear();
            $this->entityManager->close();
        }

        $this->schemaTool = null;
        $this->entityManager = null;

        parent::tearDown();
    }
}
