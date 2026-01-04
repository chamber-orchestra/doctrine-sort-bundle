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
        $hash = '';
        foreach ($values as $value) {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('c');
            } elseif (\is_array($value)) {
                $value = \serialize($value);
            } elseif (\is_object($value)) {
                $value = \spl_object_hash($value);
            }

            $hash .= $value;
        }

        return \md5($hash);
    }
}