# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ChamberOrchestra Doctrine Sort Bundle is a Symfony bundle that automatically maintains sort order consistency in Doctrine ORM entities. It uses PHP attributes to mark sort fields, supports optional grouping (e.g., per parent/category), and applies order corrections automatically during Doctrine flush operations.

## Build and Test Commands

```bash
# Install dependencies
composer install

# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Unit/Sort/SorterTest.php

# Run tests in specific directory
./vendor/bin/phpunit tests/Unit/Sort/Orm/

# Run single test method
./vendor/bin/phpunit --filter testMethodName
```

## Architecture

### Sort Attribute

**Sort** (`src/Mapping/Attribute/Sort.php`): PHP attribute applied to entity properties to mark them as sort fields. Implements Doctrine's `MappingAttribute`. Options: `groupBy` (array of field names for grouped sorting), `evictCollections` (cache collections to clear on change), `evictRegions` (query cache regions to clear).

### Mapping Layer

**SortDriver** (`src/Mapping/Driver/SortDriver.php`): Extends `AbstractMappingDriver` from metadata-bundle. Reads `#[Sort]` attributes from entity properties, validates that `groupBy` fields exist and have proper Doctrine mapping (`Column`, `ManyToMany`, or `ManyToOne`), auto-discovers inverse collection relationships for cache eviction, and handles entity inheritance by resolving to root entity name. Only processes the first `#[Sort]` attribute found per entity (returns after first match).

**SortConfiguration** (`src/Mapping/Configuration/SortConfiguration.php`): Extends `AbstractMetadataConfiguration`. Provides accessors for sort field name, grouping fields, cache eviction collections/regions, and declaring entity name. Stores configuration in inherited `$mappings` array keyed by field name.

### Event Subscriber

**SortSubscriber** (`src/EventSubscriber/SortSubscriber.php`): Extends `AbstractDoctrineListener` from metadata-bundle, registered as a Doctrine listener for `onFlush` and `postFlush` events. On flush: iterates scheduled insertions, updates, and deletions for entities with `SortConfiguration`, collects changes via `Collector`, then calls `Processor` to apply corrections. On post-flush: evicts cache collections/regions from all processed `ChangeSet`s, then resets internal state. Lazily creates `Collector`, `ChangeSetMap`, `Sorter`, and `RepositoryFactory` instances per `ObjectManager` class.

### Sort Components

**Collector** (`src/Sort/Collector.php`): Readonly class that analyzes entity changes. For insertions: extracts sort order and grouping condition via `DiffHelper`, fixes out-of-range orders. For updates: checks if sort or grouping fields changed via `DiffHelper.hasChangedFields()`, then treats updates as a deletion at old position + insertion at new position. For deletions: records removal at old position. The `fixOrder()` method clamps sort positions to `[1, maxOrder+1]`, treating `0` or `null` as "append to end".

**Sorter** (`src/Sort/Sorter.php`): Readonly class. For each `Update` in a `ChangeSet`, fetches affected entity ranges from the database as `Ds\Vector<Pair>`, removes deletions by index lookup, inserts new entries at calculated positions, then reassigns sequential sort orders starting from the range minimum.

**Processor** (`src/Sort/Processor.php`): Readonly class. Takes the `Vector<Pair>` result from `Sorter`, finds each entity by ID via EntityManager, loads proxies if uninitialized, sets the sort field value via ClassMetadata, and calls `recomputeSingleEntityChangeSet()` to ensure Doctrine flushes the update.

**DiffHelper** (`src/Sort/Orm/Helper/DiffHelper.php`): Readonly class. Extracts field changes from Doctrine's UnitOfWork. `getSortFieldChangeSet()` returns `[old, new]` sort values. `getGroupingFieldChangeSet()` returns `[oldCondition, newCondition]` arrays. `hasChangedFields()` checks if sort field or any grouping field was modified.

### ORM Value Objects

**Pair** (`src/Sort/Orm/Pair.php`): Readonly value object holding `(id, order)` — used both as DQL constructor expression results and as insertion/deletion descriptors.

**ChangeSetMap** (`src/Sort/Orm/ChangeSetMap.php`): Iterable `Ds\Map` keyed by entity class name, containing `ChangeSet` instances. Created per `ObjectManager` per flush cycle.

**ChangeSet** (`src/Sort/Orm/ChangeSet.php`): Iterable `Ds\Map` keyed by condition hash (via `Utils::hash()`), containing `Update` instances. Holds the `ClassMetadata` and `SortConfiguration` for an entity class. Extracts entity IDs from metadata when recording insertions/deletions.

**Update** (`src/Sort/Orm/Update.php`): Holds insertions and deletions (as `Pair` arrays) for a specific grouping condition. `getRanges()` merges overlapping or adjacent insertions/deletions into `Range` objects to minimize database queries.

**Range** (`src/Sort/Orm/Range.php`): Represents a contiguous block of sort positions affected by changes. Tracks min/max boundaries and merges adjacent ranges (gap of 1). Returns deletions sorted descending by order (for safe removal) and insertions sorted ascending.

### Repository Layer

**EntityRepository** (`src/Sort/Repository/EntityRepository.php`): Queries the database for `MAX(sortOrder)` with grouping conditions (cached per condition hash with auto-increment). `getCollection()` fetches `Pair` objects via DQL constructor expression for a given condition and sort range, using query cache.

**RepositoryFactory** (`src/Sort/RepositoryFactory.php`): Caches `EntityRepository` instances per entity class name.

### Entity Traits

**SortTrait** (`src/Entity/SortTrait.php`): Provides `$sortOrder` integer column with `#[Sort]` attribute and getter. Default value is `PHP_INT_MAX` (appends to end).

**SortByParentTrait** (`src/Entity/SortByParentTrait.php`): Extends the pattern with a `$parent` ManyToOne field, `#[Sort(groupBy: ['parent'])]`, and a composite index on `[parent, sortOrder]`.

**SortInterface** (`src/Contracts/Entity/SortInterface.php`): Contract requiring `getSortOrder(): int`.

### Service Configuration

Services are autowired and autoconfigured via `src/Resources/config/services.php`. The `Sort/` directory is explicitly excluded from autowiring (`autowire(false)`, `autoconfigure(false)`) — sort components (`Collector`, `Sorter`, `Processor`, etc.) are instantiated manually by `SortSubscriber`. The `EventSubscriber/` directory is tagged with `doctrine.event_subscriber`.

## Testing

- **Unit tests**: `tests/Unit/` — mirror the `src/` structure, test individual classes in isolation with mocked dependencies
- **Integration tests**: `tests/Integrational/` — test bundle integration with Symfony and Doctrine
- **Test kernel**: `tests/Integrational/TestKernel.php` boots minimal Symfony with FrameworkBundle, ChamberOrchestraMetadataBundle, ChamberOrchestraDoctrineSortBundle, and DoctrineBundle with in-memory SQLite
- **Fixtures**: `tests/Fixtures/Entity/` — `SimpleSortableEntity` (basic sort) and `GroupedSortableEntity` (parent grouping)

When writing tests, follow existing patterns: unit tests under `tests/Unit/` mirroring `src/` structure, integration tests under `tests/Integrational/` extending `IntegrationTestCase` for service wiring and Doctrine integration.

## Code Style

- PHP 8.5+ with strict types (`declare(strict_types=1);`)
- PSR-4 autoloading: `ChamberOrchestra\DoctrineSortBundle\` → `src/`
- Readonly classes for immutable value objects and stateless services
- `Ds\Vector` and `Ds\Map` from php-ds for collection operations
- Follow existing code formatting (PSR-12 conventions)

## Dependencies

- Requires PHP 8.5, Symfony 8.0 components, `chamber-orchestra/metadata-bundle` 8.0, and `php-ds/php-ds` ^1.7
- Doctrine ORM 3.6+ and DoctrineBundle 2.8+ (implicit via metadata-bundle)
- Main branch is `main`

## Workflow Orchestration

### Plan Mode Default

- Enter plan mode for ANY non-trivial task (3+ steps or architectural decisions)
- If something goes sideways, STOP and re-plan immediately — don't keep pushing
- Use plan mode for verification steps, not just building
- Write detailed specs upfront to reduce ambiguity

### Subagent Strategy

- Use subagents liberally to keep main context window clean
- Offload research, exploration, and parallel analysis to subagents
- For complex problems, throw more compute at it via subagents
- One task per subagent for focused execution

### Self-Improvement Loop

- After ANY correction from the user: update `tasks/lessons.md` with the pattern
- Write rules for yourself that prevent the same mistake
- Ruthlessly iterate on these lessons until mistake rate drops
- Review lessons at session start for relevant project

### Verification Before Done

- Never mark a task complete without proving it works
- Diff behavior between main and your changes when relevant
- Ask yourself: "Would a staff engineer approve this?"
- Run tests, check logs, demonstrate correctness

### Demand Elegance (Balanced)

- For non-trivial changes: pause and ask "is there a more elegant way?"
- If a fix feels hacky: "Knowing everything I know now, implement the elegant solution"
- Skip this for simple, obvious fixes — don't over-engineer
- Challenge your own work before presenting it

### Autonomous Bug Fixing

- When given a bug report: just fix it. Don't ask for hand-holding
- Point at logs, errors, failing tests — then resolve them
- Zero context switching required from the user
- Go fix failing CI tests without being told how
