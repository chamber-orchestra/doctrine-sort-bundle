<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
