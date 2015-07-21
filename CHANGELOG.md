# Changelog
All Notable changes to `Spider` will be documented in this file

## v0.3 - NEXT
- Refactor Drivers to hold configuration internally instead of by array
- Basic Query Builder (without traversals)
  - Where filters
  - limit and group
  - targets and projections
- Basic OrientDB CommandProcessor
- Rename all `Query` to `Command`

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