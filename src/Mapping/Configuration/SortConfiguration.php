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
use ChamberOrchestra\MetadataBundle\Mapping\ORM\EntityNameAwareInterface;

class SortConfiguration extends AbstractMetadataConfiguration implements EntityNameAwareInterface
{
    public function getSortField(): string
    {
        $this->assertMapped();

        /** @var string $key */
        $key = \array_key_first($this->mappings);

        return $key;
    }

    /**
     * @return list<string>
     */
    public function getGroupingFields(): array
    {
        return $this->getFirstMapping()['groupBy'];
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public function getEvictCacheCollections(): array
    {
        return $this->getFirstMapping()['evictCollections'];
    }

    /**
     * @return list<string>
     */
    public function getEvictCacheRegions(): array
    {
        return $this->getFirstMapping()['evictRegions'];
    }

    public function getEntityName(): string
    {
        return $this->getFirstMapping()['entityName'];
    }

    /**
     * @return array{groupBy: list<string>, evictCollections: list<array{0: string, 1: string}>, evictRegions: list<string>, entityName: string, sort: true}
     */
    private function getFirstMapping(): array
    {
        $this->assertMapped();

        /** @var array{groupBy: list<string>, evictCollections: list<array{0: string, 1: string}>, evictRegions: list<string>, entityName: string, sort: true} */
        return \current($this->mappings);
    }

    private function assertMapped(): void
    {
        if (empty($this->mappings)) {
            throw new RuntimeException('No sort field has been mapped. Call mapField() first.');
        }
    }
}
