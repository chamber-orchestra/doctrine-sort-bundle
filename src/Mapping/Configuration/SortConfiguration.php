<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\AbstractMetadataConfiguration;

class SortConfiguration extends AbstractMetadataConfiguration
{
    public function getSortField(): string
    {
        return \array_key_first($this->mappings);
    }

    public function getGroupingFields(): array
    {
        return \current($this->mappings)['groupBy'];
    }

    public function getEvictCacheCollections(): array
    {
        return \current($this->mappings)['evictCollections'];
    }

    public function getEvictCacheRegions(): array
    {
        return \current($this->mappings)['evictRegions'];
    }

    public function getEntityName(): string
    {
        return \current($this->mappings)['entityName'];
    }
}
