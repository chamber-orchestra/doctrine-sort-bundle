<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration;

use ChamberOrchestra\DoctrineSortBundle\Exception\RuntimeException;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\AbstractMetadataConfiguration;

class SortConfiguration extends AbstractMetadataConfiguration
{
    public function getSortField(): string
    {
        $this->assertMapped();

        return \array_key_first($this->mappings);
    }

    public function getGroupingFields(): array
    {
        return $this->getMappingValue('groupBy');
    }

    public function getEvictCacheCollections(): array
    {
        return $this->getMappingValue('evictCollections');
    }

    public function getEvictCacheRegions(): array
    {
        return $this->getMappingValue('evictRegions');
    }

    public function getEntityName(): string
    {
        return $this->getMappingValue('entityName');
    }

    private function getMappingValue(string $key): mixed
    {
        $this->assertMapped();

        $mapping = \current($this->mappings);

        if (!\array_key_exists($key, $mapping)) {
            throw new RuntimeException(\sprintf('Sort mapping is missing required key "%s".', $key));
        }

        return $mapping[$key];
    }

    private function assertMapped(): void
    {
        if (empty($this->mappings)) {
            throw new RuntimeException('No sort field has been mapped. Call mapField() first.');
        }
    }
}
