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
use ChamberOrchestra\DoctrineSortBundle\Entity\SortTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SimpleSortableEntity implements SortInterface
{
    use SortTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function __construct(int $id, int $sortOrder = 0)
    {
        $this->id = $id;
        $this->sortOrder = $sortOrder;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }
}
