<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

use ChamberOrchestra\DoctrineSortBundle\Contracts\Entity\SortInterface;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
class CachedChildSortableEntity implements SortInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: CachedParentEntity::class, inversedBy: 'children')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
    private ?CachedParentEntity $parent = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    #[Sort(
        groupBy: ['parent'],
        evictCollections: [[CachedParentEntity::class, 'children']],
        evictRegions: ['sort_test_region'],
    )]
    private int $sortOrder;

    public function __construct(int $id, ?CachedParentEntity $parent = null, int $sortOrder = 0)
    {
        $this->id = $id;
        $this->parent = $parent;
        $this->sortOrder = $sortOrder;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getParent(): ?CachedParentEntity
    {
        return $this->parent;
    }

    public function setParent(?CachedParentEntity $parent): void
    {
        $this->parent = $parent;
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
