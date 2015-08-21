# Command and Query Builders
Spider offers an easy way to build powerful queries in a fluent way.

For this guide, we will be using the following graph:

[image]()

Blue vertices are `person`s and green are `software`s. Each node has a property `name` which is seen.

Spider is modelled after SQL and SQL QueryBuilders, so the learning curve should be small.

```php
$person = $query
   ->select()
   ->from('person')
   ->where('name', 'vadas')
   ->first();

echo $people->name; // 'Vadas'
```

The Query builder will translate your query into a script in any language (OrientSQL, Cypher, Gremlin) that has a language processor.

There are really two query builders for you to choose from.

A [**Command builder**](#the-basic-command-builder) is a simple command builder used to generate Commands (language-specific scripts).
The Command builder has no awareness of any drivers and can't fire queries.
Instead, you can use the Command Builder to create your queries and (optionally) process them directly to an active [`Connection`](connections.md).

While the Command Builder is useful in some cases, the [**Query**](#the-query-builder) is far more powerful.
With the Query, you can build up your script, execute the query, and get the results in any supported format.
All with a simple, fluent api. Of course, the Query Builder extends the Command Builder in every way.
And, the Query has a few sugar methods that just make life easier.

## The Basic Command Builder
The Command Builder is best used when integrating Spider into your existing codebase. 
It makes no assumptions about *how* you will execute these queries.
It just builds a predictable `Command` that has a script that you may execute any way you like.

The Command Builder (for the moment) only handles CRUD operations. Traversals and relationships are on the way!

### Setup and Configuration
Simply
```php
$builder = new Spider\Commands\Builder()
```
and you're ready to go!

You can optionally pass in any Language Processor to use later.

```php
$builder = new Spider\Commands\Builder($processor);
```

#### Getting Commands and Scripts
Once you have built up your command you can process the query and return a `Command` with the language-specific script.
```php
$command = $builder->getCommand(new LanguageSpecificProcessor());
// or you can pass one to the constructor at creation
```
If you want to get a string version of your query, the `Command` lets you ```$command->getScript()``` but you could also use the `Builder` shortcut:
```php
$stringScript = $builder->getScript(new LanguageSpecificProcessor());
```

### Selects
A **Target** is the label or record type in the database. In MySQL, this would be the table.

You can select everything from a target.
```php
$builder->select()->from('person');
```

You can specify which fields or **properties** you want.
```php
$builder->select(['name', 'height'])->from('person');
```

You can select specific records by IDs
```php
$builder->select()->record($id);
$builder->select()->records([$id, $id, $id]);
$builder->select()->byId($id);
```

Feel free to set limits.
```php
$builder->select()->from('person')
    ->limit(20)

$builder->select()->from('software')
    ->first();
```

Order the results.
```php
$builder->select()->from('person')
    ->orderBy('name', 'asc') // or desc
    ->orderBy('height', 'desc')
```

#### Where Constraints
You can constrain the query using `where()`s.

```php
// A single constraint
$builder->select()->from('person')->where('name', 'marko');

// Multiple constraints
$builder->select()->from('person')->where('name', 'marko')->where('height', 6);
$builder
   ->select()->from('person')
   ->where('name', 'marko')
   ->andWhere('height', 6)
   ->orWhere('height', 5)

// Be more specific
$builder
   ->select()
   ->from('person')
   ->where(['height', '>', 5]);

$builder
    ->select()->from('person')
    ->where([
        ['name', '=', 'marko'],
        ['height', '>', 5, 'OR'] // OR WHERE height > 5. If no fourth parameter, default is AND
    ]);
```

#### Special Formats
Graph databases have extra power, and Spider takes that seriously.
You can flag a query to return a *tree* or *path*.

**Note: This is not implemented until traversals are finished**

### Inserts
Creating new records is as easy as telling a story.

```php
$builder
    ->insert(['name' => 'Zoe', 'rank' => 'corporal'])
    ->from('person'); // target

// Or
$builder
    ->insert()
    ->data(['name' => 'Zoe', 'rank' => 'corporal'])
    ->from('person'); // target
```

**Note** that for the Command builder, you can put these methods in any order you like, but
the Query builder will execute the query if you pass an `array` into `insert()`.

### Updates
Life isn't static. Things change. Let your database change with them. Updating records is simple.

```php
$builder
    ->update('status', 'active')
    ->from('person')
    ->where('name', 'marko);

// Or, use update as a target
$builder
    ->update()
    ->withData(['status' => 'cancelled'])
    ->from('person')
    ->where('name', 'marko');

// And, of course, by record
$builder
    ->update()
    ->record(3)
    ->data(['status' => 'cancelled']); // data() and withData() are aliases

// Plus limits
$builder
    ->update(['status' => 'active'])
    ->from('person')
    ->where(['height', '>', 5])
    ->limit(3);
```

### Deletes
Bye bye my baby, bye bye.

```php
// Drop records by id
$builder->drop('#12:1'); // id of the record to delete
$builder->drop([1, 2, 3]); // array of ids

// Or with all the usual constraints
$builder
    ->drop()
    ->from('software')
    ->where('name', 'ripple')
    ->limit(7);
```

## The Query Builder
The Query builder extends the Command Builder, but allows you to:
  1. Execute queries directly from the Builder
  2. [Format responses](responses.md)

### Configuring the Query builder
All `Query` needs to get going is a valid connection
```php
$query = new Query($connection);
```
When setting up the Query builder, you must pass a valid `Connection`.
You may also pass a processor as a second argument, but be sure you know what you are doing.

### Usage
The Query Builder extends the Basic [Command Builder](#builder):
```php
$query
    ->select('name')
    ->from('person')
    ->where('name', 'peter')
    ->andWhere('height', 5)
    ->limit(1)
    ->orderBy('name', 'asc') // order of orderBy is important
    ->all();
```

### Dispatching from the Query builder
What makes the Query Builder different is that you can interact with the database directly.

You can simply **dispatch** your query, or use **go**
```php
$result = $query->select()->from('person')
    ->dispatch();
      
$result = $query->select()->from('person')
    ->go();
```
Which will return a generic `Response`. Read [more about responses](responses.md).

----

**all()** removes the limit and returns a an array or collections
```php
$result = $query->select()->from('person')
    ->all();
```
Read [more about responses](responses.md).

----

Or, we recommend **get()** for most cases.
```php
$result = $query->select()->from('person')
    ->get(); // alias of set()
```
Which will return a Set (array of or single`Collection`).
Read [more about responses](responses.md).


----

**one()** and **first()** sets the limit to `1` before dispatching
```php
$result = $query->select()->from('person')
    ->one(); // or first();
```
Returns a single `Collection`. Read [more about responses](responses.md).

----

**path()** dispatches and returns a Path.
```php
$result = $query->select()->from('person')
    ->path();
```
Read [more about responses](responses.md).

**Note: This is not implemented until traversals are finished**

----

**tree()** dispatches and returns a Tree.
```php
$result = $query->select()->from('person')
    ->tree();
```
Read [more about responses](responses.md).

**Note: This is not implemented until traversals are finished**

----

**set()** dispatches and returns a Set (array or single collection).
This is really an alias for `get()`
```php
$result = $query->select()->from('person')
    ->set();
```
Read [more about responses](responses.md).

----

**scalar()** dispatches and returns a single, scalar value.
```php
$result = $query->select('name')->from('person')->where('id', 5)
    ->scalar();

echo $result; // 'Miranda'
```
Read [more about responses](responses.md).
