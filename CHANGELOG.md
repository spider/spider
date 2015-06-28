# Changelog

All Notable changes to `Spider` will be documented in this file

## v0.2 - NEXT
- Updated Driver Interface to include full api
- First Party Driver for OrientDB (v2.*) with skipable tests (dependent on database installed)
- Register configs inside Connections\Manager
- Maps all responses to instances of Graph|Record|array
- Connection Manager caches and can return already instantiated connection
- Throws `ConnectionNotFoundException` if make()ing a non-existent connection

## v0.1.1 - 4-28-2015
### Added
- Initialized contracts: DriverInterface and ConnectionInterface
- Setup connection manager
- Init Repo
- Initial Tests
