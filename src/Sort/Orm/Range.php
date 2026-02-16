<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort\Orm;

use ChamberOrchestra\DoctrineSortBundle\Exception\RuntimeException;

class Range
{
    private const int MAX_INT = 2147483647;
    private int $min = self::MAX_INT;
    private int $max = 0;
    /** @var list<Pair> */
    private array $deletions = [];
    /** @var list<Pair> */
    private array $insertions = [];

    public function __construct(?Pair $insertion, ?Pair $deletion)
    {
        $this->add($insertion, $deletion);
    }

    public function contains(?Pair $insertion, ?Pair $deletion): bool
    {
        [$min, $max] = $this->range($insertion, $deletion);

        // Expand boundaries by one to merge adjacent ranges as [x,y][y+1,z]
        $x = $this->min - 1;
        $y = $this->max + 1;

        if (($x <= $min && $min <= $y) || ($x <= $max && $max <= $y) || ($min <= $x && $max >= $y)) {
            return true;
        }

        return false;
    }

    public function add(?Pair $insertion, ?Pair $deletion): void
    {
        if (null !== $insertion) {
            $this->insertions[] = $insertion;
        }

        if (null !== $deletion) {
            $this->deletions[] = $deletion;
        }

        [$min, $max] = $this->range($insertion, $deletion);
        $this->min = \min($this->min, $min);
        $this->max = \max($this->max, $max);
    }

    /**
     * @return list<Pair>
     */
    public function getDeletions(): array
    {
        $values = $this->deletions;
        \usort($values, fn (Pair $a, Pair $b): int => $b->order <=> $a->order);

        return $values;
    }

    /**
     * @return list<Pair>
     */
    public function getInsertions(): array
    {
        $values = $this->insertions;
        \usort($values, fn (Pair $a, Pair $b): int => $a->order <=> $b->order);

        return $values;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    private function assert(?Pair $insertion, ?Pair $deletion): void
    {
        if (null === $insertion && null === $deletion) {
            throw new RuntimeException('Passed "insertion" and "deletion" values can not be simultaneously null.');
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function range(?Pair $insertion, ?Pair $deletion): array
    {
        $this->assert($insertion, $deletion);

        if (null === $insertion || null === $deletion) {
            /** @var Pair $pair */
            $pair = $insertion ?? $deletion;

            return [$pair->order, self::MAX_INT];
        }

        return [
            \min($insertion->order, $deletion->order),
            \max($insertion->order, $deletion->order),
        ];
    }
}
