<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Sort;

use ChamberOrchestra\DoctrineSortBundle\Sort\Util\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    public function testHashHandlesScalarArrayAndObject(): void
    {
        $date = new \DateTimeImmutable('2024-01-01T00:00:00+00:00');
        $object = new \stdClass();

        $hash = Utils::hash(['foo', 123, $date, ['bar' => 'baz'], $object]);

        self::assertIsString($hash);
        self::assertSame(32, \strlen($hash));
    }

    public function testHashThrowsOnUnsupportedType(): void
    {
        $resource = \fopen('php://memory', 'r');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported condition value type: resource (stream)');

        try {
            Utils::hash([$resource]);
        } finally {
            \fclose($resource);
        }
    }
}
