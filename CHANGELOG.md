# Changelog

All Notable changes to `Spider` will be documented in this file

## v0.2 - NEXT
- Updated: ConnectionInterface to mimic DriverInterface
- Updated: DriverInterface to include full api
- Added: First Party Driver for OrientDB (v2.*) with skipable tests (dependent on database installed)
- Added: Register configs inside Connections\Manager, and through the chain
- Added: Maps all responses to instances of Graph|Record|array
- Added: Connection Manager caches and can return already instantiated connection
- Added: Throws `ConnectionNotFoundException` if make()ing a non-existent connection
- Refactor: Change Driver class naming to follow a convention

## v0.1.1 - 4-28-2015
### Added
- Initialized contracts: DriverInterface and ConnectionInterface
- Setup connection manager
- Init Repo
- Initial Tests
