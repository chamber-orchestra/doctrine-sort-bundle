<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Mapping\Driver;

use ChamberOrchestra\MetadataBundle\Mapping\Driver\AbstractMappingDriver;
use ChamberOrchestra\MetadataBundle\Mapping\ExtensionMetadataInterface;
use ChamberOrchestra\MetadataBundle\Mapping\ORM\ExtensionMetadata;
use ChamberOrchestra\DoctrineSortBundle\Exception\MappingException;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;

class SortDriver extends AbstractMappingDriver
{
    private const array COLLECTION_ANNOTATIONS = [ManyToMany::class, ManyToOne::class];

    public function loadMetadataForClass(ExtensionMetadataInterface $extensionMetadata): void
    {
        $className = $extensionMetadata->getName();
        /** @var ExtensionMetadata $extensionMetadata */
        /** @var ClassMetadata $meta */
        $meta = $extensionMetadata->getOriginMetadata();
        $class = $meta->getReflectionClass();
        $inheritanceType = $meta->inheritanceType;
        $rootEntityName = $meta->rootEntityName;
        $entityName = $meta->name;

        $sortProperty = null;
        $sortAttr = null;

        foreach ($class->getProperties() as $property) {
            /** @var Sort $attr */
            if (null === $attr = $this->reader->getPropertyAttribute($property, Sort::class)) {
                continue;
            }

            if (null !== $sortProperty) {
                throw MappingException::duplicateSortAttribute($className);
            }

            $sortProperty = $property;
            $sortAttr = $attr;
        }

        if (null === $sortProperty || null === $sortAttr) {
            return;
        }

        /** @var list<string> $groups */
        $groups = $sortAttr->groupBy;
        $collections = [];
        if (\count($groups)) {
            foreach ($groups as $group) {
                if (!$class->hasProperty($group)) {
                    throw MappingException::missingProperty($className, $group, $sortProperty->getName());
                }

                $annotations = \array_merge([Column::class], self::COLLECTION_ANNOTATIONS);
                if (!$this->hasAnnotation($class, $group, $annotations)) {
                    throw MappingException::missingAttribute($className, $group, \implode(',', $annotations));
                }

                $collectionAnnotation = $this->getCollectionAnnotation($class, $group);
                if (null !== $collectionAnnotation) {
                    $collection = $this->getTargetCollection($class, $collectionAnnotation);
                    if (null !== $collection) {
                        $collections[] = $collection;
                    }
                }
            }
        }

        $declaringEntityName = $entityName;
        if ($inheritanceType !== $meta::INHERITANCE_TYPE_NONE) {
            if (\is_a($rootEntityName, $sortProperty->getDeclaringClass()->getName(), true)) {
                $declaringEntityName = $rootEntityName;
            }
        }

        $config = new SortConfiguration();
        $config->mapField($sortProperty->getName(), [
            'sort' => true,
            'groupBy' => $groups,
            'evictCollections' => \array_merge($sortAttr->evictCollections, $collections),
            'evictRegions' => \array_merge($sortAttr->evictRegions, []),
            'entityName' => $declaringEntityName,
        ]);

        $extensionMetadata->addConfiguration($config);
    }

    protected function getPropertyAttribute(): string|null
    {
        return Sort::class;
    }

    /**
     * @param list<class-string> $annotations
     */
    private function hasAnnotation(\ReflectionClass $class, string $field, array $annotations): bool
    {
        $groupProperty = $class->getProperty($field);
        foreach ($annotations as $annotation) {
            $column = $this->reader->getPropertyAttribute($groupProperty, $annotation);
            if (null !== $column) {
                return true;
            }
        }

        return false;
    }

    private function getCollectionAnnotation(\ReflectionClass $class, string $field): ManyToMany|ManyToOne|null
    {
        $property = $class->getProperty($field);
        foreach (self::COLLECTION_ANNOTATIONS as $annotation) {
            $value = $this->reader->getPropertyAttribute($property, $annotation);
            if ($value instanceof ManyToMany || $value instanceof ManyToOne) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function getTargetCollection(\ReflectionClass $rootClass, ManyToMany|ManyToOne $attr): ?array
    {
        if ($attr instanceof ManyToMany) {
            $property = $attr->mappedBy ?: $attr->inversedBy;
        } else {
            $property = $attr->inversedBy;
        }

        if (null === $property) {
            return null;
        }

        $targetEntity = $attr->targetEntity;

        if (null === $targetEntity) {
            return null;
        }

        if (!\str_contains($targetEntity, '\\')) {
            $targetEntity = $rootClass->getNamespaceName().'\\'.$targetEntity;
        }

        return [$targetEntity, $property];
    }
}
