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
use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Util\Utils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ds\Map;

/**
 * @implements \IteratorAggregate<string, Update>
 */
class ChangeSet implements \IteratorAggregate
{
    private readonly string $identifierField;

    /** @var Map<string, Update> */
    private Map $map;

    public function __construct(
        private readonly ClassMetadata $classMetadata,
        private readonly SortConfiguration $configuration,
    ) {
        $identifiers = $classMetadata->getIdentifier();

        if (\count($identifiers) !== 1) {
            throw new RuntimeException(\sprintf(
                'Entity "%s" must have exactly one identifier field, got %d. Composite primary keys are not supported.',
                $classMetadata->getName(),
                \count($identifiers),
            ));
        }

        $this->identifierField = $identifiers[0];
        $this->map = new Map();
    }

    public function getClassMetadata(): ClassMetadata
    {
        return $this->classMetadata;
    }

    public function getConfiguration(): SortConfiguration
    {
        return $this->configuration;
    }

    public function addInsertion(object $entity, int $index, array $condition): void
    {
        /** @var int|string $id */
        $id = $this->getClassMetadata()->getFieldValue($entity, $this->identifierField);
        $this->getSet($condition)->addInsertion(new Pair($id, $index));
    }

    public function addDeletion(object $entity, int $index, array $condition): void
    {
        /** @var int|string $id */
        $id = $this->getClassMetadata()->getFieldValue($entity, $this->identifierField);
        $this->getSet($condition)->addDeletion(new Pair($id, $index));
    }

    /**
     * @return \Traversable<string, Update>
     */
    public function getIterator(): \Traversable
    {
        return $this->map;
    }

    private function getSet(array $condition): Update
    {
        $hash = Utils::hash($condition);

        if (!$this->map->hasKey($hash)) {
            $this->map->put($hash, new Update($condition));
        }

        return $this->map->get($hash);
    }
}
