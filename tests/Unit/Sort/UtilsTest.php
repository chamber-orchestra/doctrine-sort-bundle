<?php

declare(strict_types=1);

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
}
