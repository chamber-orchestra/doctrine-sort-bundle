<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute;

use Attribute;
use Doctrine\ORM\Mapping\MappingAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Sort implements MappingAttribute
{
    public function __construct(
        public array $groupBy = [],
        public array $evictCollections = [],
        public array $evictRegions = []
    ) {
        foreach ($groupBy as $field) {
            if (!\is_string($field)) {
                throw new \InvalidArgumentException('Each "groupBy" element must be a string.');
            }
        }

        foreach ($evictCollections as $entry) {
            if (!\is_array($entry) || \count($entry) !== 2 || !\is_string($entry[0]) || !\is_string($entry[1])) {
                throw new \InvalidArgumentException('Each "evictCollections" element must be a 2-element array of [string, string].');
            }
        }

        foreach ($evictRegions as $region) {
            if (!\is_string($region)) {
                throw new \InvalidArgumentException('Each "evictRegions" element must be a string.');
            }
        }
    }
}
