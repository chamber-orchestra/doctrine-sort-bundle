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
class GroupedSortableEntity implements SortInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $category;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[Sort(groupBy: ['category'])]
    private int $sortOrder;

    public function __construct(int $id, string $category, int $sortOrder = 0)
    {
        $this->id = $id;
        $this->category = $category;
        $this->sortOrder = $sortOrder;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function moveUp(): void
    {
        $this->sortOrder = \max(1, $this->sortOrder - 1);
    }

    public function moveDown(): void
    {
        ++$this->sortOrder;
    }

    public function moveToBeginning(): void
    {
        $this->sortOrder = 1;
    }

    public function moveToEnd(): void
    {
        $this->sortOrder = 0;
    }
}
