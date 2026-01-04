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
final class Sort implements MappingAttribute
{
    /**
     * @var array $evictCollections dependent cache collections  ["FQCN" => "collection"]
     */
    public function __construct(
        public array $groupBy = [],
        public array $evictCollections = [],
        public array $evictRegions = []
    )
    {
    }
}
