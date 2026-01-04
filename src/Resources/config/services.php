<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $services->load('ChamberOrchestra\\DoctrineSortBundle\\', __DIR__.'/../../*')
        ->exclude(__DIR__.'/../../{DependencyInjection,Resources,ExceptionInterface,Repository}');

    $services->load('ChamberOrchestra\\DoctrineSortBundle\\EventSubscriber\\', __DIR__.'/../../EventSubscriber/')
        ->tag('doctrine.event_subscriber');

    $services->load('ChamberOrchestra\\DoctrineSortBundle\\Sort\\', __DIR__.'/../../Sort/')
        ->autowire(false)
        ->autoconfigure(false);
};
