<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort\Util;

class Utils
{
    public static function hash(array $values): string
    {
        $parts = [];
        foreach ($values as $value) {
            if (null === $value) {
                $parts[] = 'n';
            } elseif ($value instanceof \DateTimeInterface) {
                $parts[] = 'd:' . $value->format('c');
            } elseif (\is_array($value)) {
                $parts[] = 'a:' . \serialize($value);
            } elseif (\is_object($value)) {
                $parts[] = 'o:' . \spl_object_hash($value);
            } else {
                $parts[] = 's:' . $value;
            }
        }

        return \hash('xxh128', \implode('|', $parts));
    }
}
