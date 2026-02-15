<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Exception;

class MappingException extends \ChamberOrchestra\MetadataBundle\Exception\MappingException
{
    public static function duplicateSortAttribute(string $className): self
    {
        return new self(\sprintf('Class "%s" has multiple #[Sort] attributes, but only one is allowed per entity.', $className));
    }
}