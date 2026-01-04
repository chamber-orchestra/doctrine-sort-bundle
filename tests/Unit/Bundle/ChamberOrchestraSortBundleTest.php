<?php

declare(strict_types=1);

namespace Tests\Unit\Bundle;

use ChamberOrchestra\DoctrineSortBundle\ChamberOrchestraDoctrineSortBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ChamberOrchestraSortBundleTest extends TestCase
{
    public function testBundleExtendsSymfonyBundle(): void
    {
        $bundle = new ChamberOrchestraDoctrineSortBundle();

        self::assertInstanceOf(Bundle::class, $bundle);
    }
}
