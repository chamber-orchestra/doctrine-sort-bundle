[![PHP Composer](https://github.com/chamber-orchestra/doctrine-sort-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/chamber-orchestra/doctrine-sort-bundle/actions/workflows/php.yml)
[![codecov](https://codecov.io/gh/chamber-orchestra/doctrine-sort-bundle/graph/badge.svg)](https://codecov.io/gh/chamber-orchestra/doctrine-sort-bundle)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%206-brightgreen)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/chamber-orchestra/doctrine-sort-bundle/v)](https://packagist.org/packages/chamber-orchestra/doctrine-sort-bundle)
[![License](https://poser.pugx.org/chamber-orchestra/doctrine-sort-bundle/license)](https://packagist.org/packages/chamber-orchestra/doctrine-sort-bundle)
![Symfony 8](https://img.shields.io/badge/Symfony-8-purple?logo=symfony)

# Doctrine Sort Bundle

Symfony bundle for automatic sort order management in Doctrine ORM entities. Recalculates sort positions on every `EntityManager::flush()`, keeping ordered lists consistent without manual reindexing. Uses PHP attributes to mark the sort field, supports grouped ordering (e.g., per parent or category), and handles insertions, deletions, and reordering transparently.

## Features

- **Automatic reordering** — sort positions are recalculated on flush, no manual gaps or renumbering needed
- **Grouped sorting** — maintain independent sort orders per parent, category, or any relation via `groupBy`
- **PHP attribute configuration** — single `#[Sort]` attribute on your entity property, zero YAML/XML config
- **Drag-and-drop ready** — set the new position and flush; surrounding items shift automatically
- **Second-level cache support** — works with Doctrine SLC; optionally evict cache collections and query regions on sort changes
- **Change tracking policies** — compatible with both `DEFERRED_IMPLICIT` and `DEFERRED_EXPLICIT` tracking
- **Provided traits** — `SortTrait` and `SortByParentTrait` with convenience methods (`moveUp`, `moveDown`, `moveToBeginning`, `moveToEnd`)

## Installation

```bash
composer require chamber-orchestra/doctrine-sort-bundle
```

If Symfony Flex is enabled, the bundle is registered automatically. Otherwise, add it to `config/bundles.php`:

```php
return [
    // ...
    ChamberOrchestra\DoctrineSortBundle\ChamberOrchestraDoctrineSortBundle::class => ['all' => true],
];
```

## Quick Start

### 1. Mark a sort field

Add the `#[Sort]` attribute to any integer column. The bundle will automatically maintain sequential ordering starting from 1.

```php
use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TodoItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    #[Sort]
    private int $sortOrder = 0;

    // Setting sortOrder to 0 appends the item to the end of the list.
    // Setting sortOrder to 1 moves it to the beginning.
    // Any value in between inserts at that position; surrounding items shift automatically.
}
```

### 2. Grouped ordering

Maintain separate sort sequences per parent, category, or any relation:

```php
use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Task
{
    #[ORM\ManyToOne]
    private ?Project $project = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    #[Sort(groupBy: ['project'])]
    private int $sortOrder = 0;

    // Each project has its own independent sort sequence.
    // Moving a task to a different project removes it from the old sequence
    // and inserts it into the new one.
}
```

### 3. Using traits

For common cases, use the provided traits instead of writing boilerplate:

```php
use ChamberOrchestra\DoctrineSortBundle\Contracts\Entity\SortInterface;
use ChamberOrchestra\DoctrineSortBundle\Entity\SortTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TodoItem implements SortInterface
{
    use SortTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
}
```

`SortTrait` provides `getSortOrder()`, `setSortOrder()`, and convenience methods:

| Method | Effect |
|---|---|
| `moveUp()` | Decrease sort position by 1 (minimum 1) |
| `moveDown()` | Increase sort position by 1 |
| `moveToBeginning()` | Set sort position to 1 |
| `moveToEnd()` | Set sort position to 0 (appends to end) |

`SortByParentTrait` extends `SortTrait` with a `$parent` ManyToOne field and `#[Sort(groupBy: ['parent'])]`.

## Attribute Options

```php
#[Sort(
    groupBy: ['parent'],           // Fields that define independent sort groups
    evictCollections: [             // Second-level cache collections to evict on change
        [ParentEntity::class, 'children'],
    ],
    evictRegions: ['my_query_region'], // Query cache regions to evict on change
)]
```

| Option | Type | Default | Description |
|---|---|---|---|
| `groupBy` | `array` | `[]` | Entity field names that define sort groups. Each unique combination of group values has its own sort sequence. Supports `Column`, `ManyToOne`, and `ManyToMany` fields. |
| `evictCollections` | `array` | `[]` | List of `[FQCN, collectionField]` pairs. These Doctrine second-level cache collections are evicted when sort order changes. |
| `evictRegions` | `array` | `[]` | Query cache region names to evict when sort order changes. |

## How It Works

1. The bundle listens to Doctrine's `onFlush` event
2. For each entity with a `#[Sort]` attribute that was inserted, updated, or deleted, it collects the changes
3. Overlapping changes within the same group are merged into ranges to minimize database queries
4. Affected entities are fetched, reordered in memory, and sort values are reassigned sequentially
5. Doctrine's `recomputeSingleEntityChangeSet()` ensures the corrected values are persisted in the same flush

This means you never need to manually manage gaps, shift items, or renumber sequences — just set the desired position and flush. Multiple reorder operations within a single flush or across multiple flushes in an explicit transaction are fully supported.

## Compatibility

| Bundle | PHP | Symfony | Doctrine ORM |
|---|---|---|---|
| 8.0 | ^8.5 | 8.0 | 3.6+ |

## Running Tests

```bash
composer test
```

## License

MIT
