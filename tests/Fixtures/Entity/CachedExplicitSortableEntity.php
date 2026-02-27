<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Entity;

use ChamberOrchestra\DoctrineSortBundle\Contracts\Entity\SortInterface;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class CachedExplicitSortableEntity implements SortInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    #[Sort]
    private int $sortOrder;

    public function __construct(int $id, int $sortOrder = 0)
    {
        $this->id = $id;
        $this->sortOrder = $sortOrder;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }
}
