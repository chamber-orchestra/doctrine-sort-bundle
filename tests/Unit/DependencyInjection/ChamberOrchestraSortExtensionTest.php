<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\DependencyInjection;

use ChamberOrchestra\DoctrineSortBundle\DependencyInjection\ChamberOrchestraDoctrineSortExtension;
use ChamberOrchestra\DoctrineSortBundle\EventSubscriber\SortSubscriber;
use ChamberOrchestra\DoctrineSortBundle\Sort\Processor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ChamberOrchestraSortExtensionTest extends TestCase
{
    public function testLoadRegistersServices(): void
    {
        $container = new ContainerBuilder();

        $extension = new ChamberOrchestraDoctrineSortExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition(SortSubscriber::class));
        self::assertTrue($container->hasDefinition(Processor::class));
    }
}
