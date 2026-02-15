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

trait SortTrait
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['unsigned' => true, 'default' => 0])]
    #[Dev\Sort]
    protected int $sortOrder = 0;

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
        $this->sortOrder = $this->sortOrder + 1;
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
