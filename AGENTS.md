# Repository Guidelines

## Project Structure & Module Organization
- `src/` contains the Symfony bundle code, organized by namespace (e.g., `Sort/`, `Mapping/`, `DependencyInjection/`, `EventSubscriber/`).
- `src/Resources/` holds bundle resources such as service config and translations.
- `tests/` is reserved for PHPUnit tests (currently empty).
- `bin/phpunit` and `vendor/` are Composer-managed tooling and dependencies.

## Build, Test, and Development Commands
- `composer install` installs PHP dependencies for local development.
- `composer test` runs the PHPUnit suite via the Composer script.
- `vendor/bin/phpunit` runs PHPUnit directly using `phpunit.xml.dist`.

## Coding Style & Naming Conventions
- PHP namespace root is `ChamberOrchestra\SortBundle` (PSR-4 via `composer.json`).
- Class files follow `StudlyCaps` with one class per file under `src/`.
- No formatter or linter is configured in this repo; keep changes consistent with existing code style and PHP 8.4 syntax.

## Testing Guidelines
- Tests should live in `tests/` and follow PHPUnit conventions (e.g., `SomethingTest.php`).
- Configure test services and kernel settings via `phpunit.xml.dist` as needed.
- Keep unit tests focused on sorter behavior and ORM helpers; add integration tests for bundle wiring.

## Commit & Pull Request Guidelines
- There is no commit history yet, so no established message convention.
- Use clear, imperative commit subjects (e.g., "Add range diff helper") and include scope when helpful.
- PRs should describe the change, list test coverage, and call out any behavior changes.

## Configuration Notes
- PHP 8.4 is required (see `composer.json`).
- Bundle services are defined in `src/Resources/config/services.php`.
