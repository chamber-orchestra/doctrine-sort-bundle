<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Entity;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute as Dev;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Index(fields: ["parent", "sortOrder"])]
trait SortByParentTrait
{
    #[ORM\ManyToOne()]
    protected object|null $parent = null;
    #[ORM\Column(type: 'integer', nullable: false, options: ['unsigned' => true])]
    #[Dev\Sort(groupBy: ['parent'])]
    protected int $sortOrder = PHP_INT_MAX;

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}
