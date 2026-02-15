<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integrational;

use ChamberOrchestra\MetadataBundle\ChamberOrchestraMetadataBundle;
use ChamberOrchestra\DoctrineSortBundle\ChamberOrchestraDoctrineSortBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new ChamberOrchestraMetadataBundle(),
            new ChamberOrchestraDoctrineSortBundle(),
            new DoctrineBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test_secret',
            'test' => true,
        ]);
        $container->extension('doctrine', [
            'dbal' => [
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [
                'second_level_cache' => [
                    'enabled' => true,
                ],
                'mappings' => [
                    'Tests' => [
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Fixtures/Entity',
                        'prefix' => 'Tests\\Fixtures\\Entity',
                        'alias' => 'Tests',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
        $container->extension('chamber_orchestra_metadata', []);
        $container->extension('chamber_orchestra_doctrine_sort', []);
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
