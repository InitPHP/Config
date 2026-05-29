# Changelog

All notable changes to `initphp/config` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0]

A correctness and modernisation release. It contains intentional
backward-incompatible changes; review the **Changed** and **Removed**
sections before upgrading from 1.x.

### Added

- `InitPHP\Config\AbstractConfig` — shared base implementing the common
  read/write contract for both `Library` and `Classes`.
- `InitPHP\Config\Exceptions\ConfigException` — a dedicated
  `RuntimeException` with named constructors, thrown by every loader on
  failure (previously a generic `\Exception` was used).
- `Library::replace()` — discard the whole tree and install a new one.
- `Library::remove()` and `Config::remove()` — removal is now reachable
  from every entry point (it previously existed only on `Classes`).
- `Config::reset()` — discard the shared facade instance (chiefly for
  test isolation).
- Full test suite (PHPUnit), static analysis (PHPStan level 8), coding
  standard (PHP-CS-Fixer), and a GitHub Actions CI pipeline.
- Developer documentation under `docs/`.

### Changed

- **Requires PHP 8.1+** (was 7.4+).
- **Requires `initphp/parameterbag` `^2.0`** (was `^1.0`). The package now
  explicitly opts into case-insensitive keys so the documented behaviour
  is preserved against ParameterBag v2's case-sensitive default.
- `setClass()` now imports an **object's runtime property values** when
  given an instance (it previously imported class defaults in both
  cases).
- `setArray()` / `setFile()` with a `null`/empty name now **merge into the
  root** instead of replacing the entire tree. Use `Library::replace()`
  for the old replace-all behaviour.
- `Library::close()` now resets to a usable empty bag (dotted-path and
  case-insensitive options preserved) instead of leaving the instance in
  an unusable state.

### Fixed

- `setDir()` applied its name prefix with inverted logic, so files were
  loaded under the wrong keys (e.g. `setDir('app', …)` did not produce
  `app.*` keys). The prefix is now applied correctly.
- Object-style access (`$library->db`) silently collapsed every array
  element onto a single property, returning only the last value. It now
  builds a correct, recursively nested `stdClass`.
- The `Config` facade tied the shared singleton's lifetime to instance
  destruction, which could leave it in a fatal, unusable state. The
  facade is now a pure static utility with explicit `reset()`.

### Removed

- The `Config` facade's instance API (`__construct`, `__destruct`,
  `__get`, `__call`, `__debugInfo`). The facade is now static-only and
  cannot be instantiated.
- `Library::set(null, …)` as a replace-all shortcut — use
  `Library::replace()`.

## [1.0]

- Initial release.
