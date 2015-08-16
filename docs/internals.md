# Internals
Notes on architecture and internal development

See also the api documentation

## Glossary of Terms
When possible, we rely on TinkerPop definitions, since they are more standard.

It is also worth noting that the `Commands\Builder` is modeled after OrientDb's SQL.

* **properties**: individual fields from a record like a `User`'s username, birthday, and age.
* **metaProperties**: record properties about the record itself (label, cluster, etc)
* **label**: The type of record (analagous to table in SQL). Class in OrientDB.
* **config**: Configuration for any object. Uses especially in the driver. What the driver needs to connect and execute against a datastore. Could include hostname, url, username, etc. Specific to driver. Both required and optional properties.
* **vertex**: A single node record in a graph
* **edge**: a relationship record in a graph
* **`Response`**: A consistent data transport object for database results. Wraps the driver and raw response.
* **`Command`** not be aware of the context (not aware of either the driver or the connection) It's just a language building tool. `Command::getScript('language')` provided the proper CommandProcessor exists for that language.
* **`Driver`**s are responsible for mapping results to a consistent format. 
They provide several mapping methods (Query::all(), Query::one(), Query::tree() etc..)
* **`Model`** is aware of the context and is a direct correlation to a *label*, connection/driver/etc.
* **`Commands\Builder`**: A basic, fluent command builder with no connection to the datastore
* **`Commands\Query`**: An enhanced Command Builder that can dispatch commands and format responses

## Exceptions
  * `FormattingException` - If requesting a format not registered.
  * `ConnectionNotFoundException` - If a non-existing connection is `make()` by the Manager
  * `InvalidCommandException` - If the Command or CommandBag is corrupt or invalid.
  * `NotSupportedException` - For cases where functionalities in some graph DBs aren't supported.
  * `ItemNotFoundException` - Whenever a user tries to get a property that doesnt' exist
