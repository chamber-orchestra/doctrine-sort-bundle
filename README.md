[![PHP Composer](https://github.com/chamber-orchestra/doctrine-sort-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/chamber-orchestra/doctrine-sort-bundle/actions/workflows/php.yml)

# DoctrineSortBundle

A Symfony bundle that keeps ordered entities consistent in Doctrine ORM by recalculating sort positions on flush. It uses metadata attributes to mark the sort field, supports optional grouping (e.g., per parent/category), and applies order corrections automatically.

## Installation

```bash
composer require chamber-orchestra/doctrine-sort-bundle
```

Enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    ChamberOrchestra\DoctrineSortBundle\ChamberOrchestraDoctrineSortBundle::class => ['all' => true],
];
```

## Usage

### Mark a sort field

```php
use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[Sort]
    private int $sortOrder = PHP_INT_MAX;
}
```

### Grouped ordering

```php
use ChamberOrchestra\DoctrineSortBundle\Mapping\Attribute\Sort;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Child
{
    #[ORM\ManyToOne]
    private ?ParentEntity $parent = null;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[Sort(groupBy: ['parent'])]
    private int $sortOrder = PHP_INT_MAX;
}
```

You can also reuse the provided traits:

- `ChamberOrchestra\DoctrineSortBundle\Entity\SortTrait`
- `ChamberOrchestra\DoctrineSortBundle\Entity\SortByParentTrait`

## Configuration

The bundle registers its services automatically. No custom configuration is required for basic use. The attribute supports optional cache eviction lists via `evictCollections` and `evictRegions`.

## Dependencies

Runtime:
- PHP ^8.4
- `chamber-orchestra/metadata-bundle`
- `php-ds/php-ds`

Symfony projects typically also use `doctrine/doctrine-bundle` and `doctrine/orm`.

## Running Tests

```bash
composer test
```

This runs PHPUnit using `phpunit.xml.dist`.
