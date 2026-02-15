<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort\Orm;

class Update
{
    private array $insertions = [];
    private array $deletions = [];

    public function __construct(
        private readonly array $condition = [],
    ) {
    }

    public function addInsertion(Pair $pair): void
    {
        $this->insertions[] = $pair;
    }

    public function addDeletion(Pair $pair): void
    {
        $this->deletions[] = $pair;
    }

    public function getCondition(): array
    {
        return $this->condition;
    }

    /**
     * @return Range[]
     */
    public function getRanges(): array
    {
        $sort = fn (Pair $a, Pair $b): int => $a->order <=> $b->order;
        $insertions = $this->insertions;
        \usort($insertions, $sort);

        $deletions = $this->deletions;
        \usort($deletions, $sort);

        $ranges = [];
        $count = \max(\count($deletions), \count($insertions));
        for ($i = 0; $i < $count; ++$i) {
            $range = $this->getMatchedRange($ranges, $ins = ($insertions[$i] ?? null), $del = ($deletions[$i] ?? null));
            if (null !== $range) {
                $range->add($ins, $del);
                continue;
            }
            $ranges[] = new Range($ins, $del);
        }

        return $ranges;
    }

    /**
     * @param Range[] $ranges
     */
    private function getMatchedRange(array $ranges, ?Pair $insertion, ?Pair $deletion): ?Range
    {
        return \array_find($ranges, fn (Range $range) => $range->contains($insertion, $deletion));

    }
}
