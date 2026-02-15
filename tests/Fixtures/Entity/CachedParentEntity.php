<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
class CachedParentEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToMany(targetEntity: CachedChildSortableEntity::class, mappedBy: 'parent')]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
    private Collection $children;

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->children = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }
}
