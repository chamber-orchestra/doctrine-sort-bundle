<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Repository\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class RepositoryFactory
{
    private array $map = [];

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function getRepository(ClassMetadata $classMetadata, SortConfiguration $configuration): EntityRepository
    {
        return $this->map[$classMetadata->getName()] ??= new EntityRepository($this->em, $classMetadata, $configuration);
    }
}