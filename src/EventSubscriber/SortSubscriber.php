<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\EventSubscriber;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Collector;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSetMap;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Helper\DiffHelper;
use ChamberOrchestra\DoctrineSortBundle\Sort\Processor;
use ChamberOrchestra\DoctrineSortBundle\Sort\RepositoryFactory;
use ChamberOrchestra\DoctrineSortBundle\Sort\Sorter;
use ChamberOrchestra\MetadataBundle\EventSubscriber\AbstractDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\ManagerEventArgs;
use Doctrine\Persistence\ObjectManager;

#[AsDoctrineListener(Events::onFlush)]
#[AsDoctrineListener(Events::postFlush)]
class SortSubscriber extends AbstractDoctrineListener
{
    /**
     * @var array<class-string, Collector>
     */
    private array $collectors = [];
    /**
     * @var array<class-string, ChangeSetMap<ChangeSet>>
     */
    private array $changeSetMaps = [];
    /**
     * @var array<class-string, Sorter>
     */
    private array $sorters = [];
    private array $repositoryFactory = [];

    public function __construct(
        private readonly Processor $processor = new Processor(),
    )
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $collector = null;
        $map = null;

        foreach ($this->getScheduledEntityInsertions($em, $class = SortConfiguration::class) as $arg) {
            ($collector ??= $this->getCollector($args))->addInsertion(($map ??= $this->getChangeSetMap($args)), $arg);
        }
        foreach ($this->getScheduledEntityUpdates($em, $class) as $arg) {
            ($collector ??= $this->getCollector($args))->addUpdateIfNeeded(($map ??= $this->getChangeSetMap($args)), $arg);
        }
        foreach ($this->getScheduledEntityDeletions($em, $class) as $arg) {
            ($collector ??= $this->getCollector($args))->addDeletion(($map ??= $this->getChangeSetMap($args)), $arg);
        }

        if (null !== $map) {
            $this->recover($map, $args);
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (null !== $cache = $args->getObjectManager()->getCache()) {
            /** @var ChangeSet $changeSet */
            foreach ($this->getChangeSetMap($args) as $changeSet) {
                foreach ($changeSet->getConfiguration()->getEvictCacheCollections() as $collection) {
                    $cache->evictCollectionRegion($collection[0], $collection[1]);
                }
                foreach ($changeSet->getConfiguration()->getEvictCacheRegions() as $region) {
                    $cache->evictQueryRegion($region);
                }
            }
        }

        $this->changeSetMaps = [];
        $this->sorters = [];
        $this->collectors = [];
    }

    private function recover(ChangeSetMap $map, ManagerEventArgs $args): void
    {
        foreach ($map as $set) {
            $this->processor->setCorrectOrder(
                $args->getObjectManager(),
                $set,
                $this->getSorter($args)->sort($set)
            );
        }
    }

    private function getRepositoryFactory(ObjectManager $em): RepositoryFactory
    {
        return $this->repositoryFactory[\get_class($em)] ??= new RepositoryFactory($em);
    }

    private function getCollector(ManagerEventArgs $args): Collector
    {
        return $this->collectors[\get_class($em = $args->getObjectManager())] ??= new Collector(
            $this->getRepositoryFactory($em),
            new DiffHelper($em),
        );
    }

    private function getChangeSetMap(ManagerEventArgs $args): ChangeSetMap
    {
        return $this->changeSetMaps[\get_class($args->getObjectManager())] ??= new ChangeSetMap();
    }

    private function getSorter(ManagerEventArgs $args): Sorter
    {
        return $this->sorters[\get_class($args->getObjectManager())] ??= new Sorter($this->getRepositoryFactory($args->getObjectManager()));
    }
}
