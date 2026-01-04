<?php

declare(strict_types=1);

namespace Tests\Integrational;

use ChamberOrchestra\DoctrineSortBundle\ChamberOrchestraDoctrineSortBundle;
use ChamberOrchestra\DoctrineSortBundle\DependencyInjection\ChamberOrchestraDoctrineSortExtension;
use ChamberOrchestra\DoctrineSortBundle\EventSubscriber\SortSubscriber;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Driver\SortDriver;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class KernelWiringTest extends IntegrationTestCase
{
    public function testBundleIsRegistered(): void
    {
        $bundleClasses = \array_map(static fn($bundle) => $bundle::class, self::$kernel->getBundles());

        self::assertContains(ChamberOrchestraDoctrineSortBundle::class, $bundleClasses);
    }

    public function testCoreServicesAreAvailable(): void
    {
        $container = self::getContainer();

        self::assertInstanceOf(SortSubscriber::class, $container->get(SortSubscriber::class));
        self::assertInstanceOf(SortDriver::class, $container->get(SortDriver::class));
    }

    public function testExtensionRegistersServiceDefinitions(): void
    {
        $container = new ContainerBuilder();

        $extension = new ChamberOrchestraDoctrineSortExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition(SortSubscriber::class));
    }
}
