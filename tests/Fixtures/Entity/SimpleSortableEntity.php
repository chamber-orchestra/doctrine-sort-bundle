<?php

declare(strict_types=1);

namespace Tests\Fixtures\Entity;

use ChamberOrchestra\DoctrineSortBundle\Contracts\Entity\SortInterface;
use ChamberOrchestra\DoctrineSortBundle\Entity\SortTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SimpleSortableEntity implements SortInterface
{
    use SortTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
