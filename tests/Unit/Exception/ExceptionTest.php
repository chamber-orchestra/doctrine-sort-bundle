<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Exception;

use ChamberOrchestra\DoctrineSortBundle\Exception\ExceptionInterface;
use ChamberOrchestra\DoctrineSortBundle\Exception\MappingException;
use ChamberOrchestra\DoctrineSortBundle\Exception\RuntimeException;
use ChamberOrchestra\MetadataBundle\Exception\MappingException as MetadataMappingException;
use PHPUnit\Framework\TestCase;

final class ExceptionTest extends TestCase
{
    public function testRuntimeExceptionImplementsInterface(): void
    {
        $exception = new RuntimeException('Boom');

        self::assertInstanceOf(RuntimeException::class, $exception);
        self::assertInstanceOf(ExceptionInterface::class, $exception);
    }

    public function testMappingExceptionExtendsMetadataException(): void
    {
        $exception = new MappingException('mapping');

        self::assertInstanceOf(MetadataMappingException::class, $exception);
    }
}
