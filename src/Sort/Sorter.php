<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort;

use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Range;
use Ds\Vector;

readonly class Sorter
{
    public function __construct(
        private RepositoryFactory $factory
    ) {
    }

    /**
     * @return Vector<Pair>
     */
    public function sort(ChangeSet $set): Vector
    {
        $er = $this->factory->getRepository($set->getClassMetadata(), $set->getConfiguration());
        /** @var Vector<Pair> $result */
        $result = new Vector();
        foreach ($set as $update) {
            foreach ($update->getRanges() as $range) {
                $vector = $er->getCollection($update->getCondition(), $range->getMin(), $range->getMax());
                $result = $result->merge($this->applyChanges($vector, $range));
            }
        }

        return $result;
    }

    /**
     * @param Vector<Pair> $vector
     *
     * @return Vector<Pair>
     */
    private function applyChanges(Vector $vector, Range $range): Vector
    {
        $deleteIds = [];
        foreach ($range->getDeletions() as $deletion) {
            $deleteIds[$deletion->id] = true;
        }
        $vector = $vector->filter(static fn (Pair $pair): bool => !isset($deleteIds[$pair->id]));

        $base = \max(1, $range->getMin());
        $length = \count($vector);
        foreach ($range->getInsertions() as $insertion) {
            $idx = \max(0, \min($insertion->order - $base, $length++));
            $vector->insert($idx, $insertion);
        }

        /** @var Vector<Pair> $result */
        $result = new Vector();
        foreach ($vector as $order => $value) {
            $result[] = new Pair($value->id, $order + $base);
        }

        return $result;
    }
}
