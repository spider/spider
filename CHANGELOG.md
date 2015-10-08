# Changelog
All Notable changes to `Spider` will be documented in this file

## v0.3.2 - 10-8-2015
- Fixes dependency versions in composer.json
- Fixes bug #91: Gremlin-PHP updated version was breaking change

## v0.3.1 - 9-1-2015
- Fixes bug #63: Set ordering in tests for Neo4j
- Cleans up scrutinizer

## v0.3.0 - 8-22-2015
- Basic Query Builder (without traversals)
  - Where filters
  - limit and group
  - targets and projections
- OrientDB CommandProcessor
- Neo4j CommandProcessor
- Consistent Response Formats
- Implement `Object` and `Collection` classes
- Manager creates Connection from array of properties
- Refactor Drivers to hold configuration internally instead of by array
- Refactor and split CommandBuilder to BaseBuilder, Builder, and Query
- Introduce fixtures, integration tests, and proper stubs.
- Better documentation, both guides and api.

## v0.2.1 - 7-9-2015
- Updated: ConnectionInterface to mimic DriverInterface
- Refactor: Change Driver class naming to follow a convention
- Refactor: Rename QueryInterface to CommandInterface to better describe all commands

## v0.2 - 6-28--2015
- Updated: DriverInterface to include full api
- Added: First Party Driver for OrientDB (v2.*) with skipable tests (dependent on database installed)
- Added: Register configs inside Connections\Manager, and through the chain
- Added: Maps all responses to instances of Graph|Record|array
- Added: Connection Manager caches and can return already instantiated connection
- Added: Throws `ConnectionNotFoundException` if make()ing a non-existent connection

## v0.1.1 - 4-28-2015
### Added
- Initialized contracts: DriverInterface and ConnectionInterface
- Setup connection manager
- Init Repo
- Initial Tests