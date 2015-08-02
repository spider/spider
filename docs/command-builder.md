# Command and Query Builders
Spider offers an easy way to build powerful queries in a fluent way.
It is modelled after SQL and SQL QueryBuilders, so the learning curve should be small.

```php
$character = $query
   ->select() // selects everything
   ->from('characters')
   ->where('allegiance', 'browncoats')
   ->first();
   
echo $character->name; // 'Mal'
```

The Query builder will translate your query into a script in any language (OrientSQL, Cypher, Gremlin) that has a language processor.

There are really two query builders for you to choose from. 

A [**Command builder**](#the-basic-command-builder) is a simple command builder used to generate Commands (language-specific scripts).
The Command builder has no awareness of any drivers and can't fire queries.
Instead, you can use the Command Builder to create your queries and (optionally) process them into a Command Object that can be sent directly to the database or through a Spider Connection.

While the Command Builder is useful in some cases, the [**Query**](#the-query-builder) is far more powerful.
With the Query, you can build up and execute the query, and get the results in any supported format.
All with a simple, fluent api.
And, the Query has a few sugar methods that just make life easier.

## The Basic Command Builder
The Command Builder is best used when integrating Spider into your existing codebase. It makes no assumptions about *how* you will execute these queries.
It just builds a predictable Command Bag and (optionally) returns the finished script that you may execute any way you like.

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
Once you have built up your command, you can get the `CommandBag` which will describe your query in a predictable way
```php
$bag = $builder->getCommandBag();
```

Or, you can process the query and return a `Command` with the language-specific script.
```php
$command = $builder->getScript(new LanguageSpecificProcessor());
// or you can pass one to the constructor at creation
```

The `Command` lets you ```$command->getScript()```

### Selects
A **Target** is the label or record type in the database. In MySQL, this would be the row.

You can select everything from a target.
```php
$builder->select()->from('planets'); 
```

You can specify which fields or **properties** you want.
```php
$builder->select('name, type')->from('planets');
$builder->select(['name', 'type'])->from('planets');
```

You can select specific records by IDs
```php
$builder->select()->record($id);
$builder->select()->records([$id, $id, $id]);
$builder->select()->byId($id);
```

Feel free to set limits.
```php
$builder->select()->from('planets')
    ->limit(20)

$builder->select()->from('planets')
    ->first();
```

Group and/or order the results.
```php
$builder->select()->from('characters')
    ->groupBy('name', 'birthday'); // or (['name', 'birthday'])
    
$builder->select()->from('users')
    ->orderBy('name', 'birthday'); // or (['name', 'birthday'])
    ->desc(); // or asc(); Defaults to ascending
```

#### Where Constraints
You can constrain the query using `where()`s.

```php
// A single constraint
$builder->select()->from('users')->where('username', 'michael'); // username = 'michael'

// Multiple constraints
$builder->select()->from('users')->where('username', 'michael')->where('place', 'Mars');
$builder
   ->select()->from('users')
   ->where('place', 'Mars')
   ->andWhere('birthday', 'July')
   ->orWhere('birthday', 'August')

// Be more specific
$builder
   ->select()
   ->from('users')
   ->where(['age', '>', 20]);

$builder
    ->select()->from('users')
    ->where([
        ['name', '=', 'michael'],
        ['age', '>', 20, 'OR'] // OR WHERE age > 20. If no fourth parameter, default is AND
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
    ->into('browncoats')
    ->insert(['name' => 'Zoe', 'rank' => 'corporal']);
    
// Or
$builder
    ->insert()
    ->data(['name' => 'Zoe', 'rank' => 'corporal'])
    ->into('browncoats')
```

**Note** that for the Command builder, you can put these methods in any order you like, but
the Query builder will execute the query if you pass an `array` into `insert()`.

### Updates
Life isn't static. Things change. Let your database change with them. Updating records is simple.

```php
$builder
    ->update('status', 'cancelled by evil Fox')
    ->from('shows')
    ->where('title', 'Firefly);
    
// Or, use update as a target
$builder
    ->update('shows')
    ->withData(['status' => 'cancelled by evil Fox'])
    ->where('title', 'Firefly');

// And, of course, by record
$builder
    ->update()
    ->record(3)
    ->data(['status' => 'cancelled by evil Fox']); // data() and withData() are aliases

// Plus limits
$builder
    ->update(['status' => 'cancelled too soon'])
    ->from('shows')
    ->where(['awesomeness', '>', 200])
    ->limit(3);
    
$builder
    ->updateFirst('shows') // target
    ->withData(['status' => 'cancelled too soon']);
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
    ->from('planets')
    ->where('name', 'Persephone')
    ->limit(7);
```

## The Query Builder
The Query builder extends the Command Builder, but allows you to:
  1. Execute queries directly from the Builder
  2. [Format responses](responses.md)
  3. Use some extra sugar to make everything more fluent.
  
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
    ->from('ships')
    ->where('class', 'firefly')
    ->andWhere('captain', 'mal')
    ->limit(1)
    ->orderBy('registry')
    ->groupBy('launch_date')
```

### Dispatching from the Query builder
What makes the Query Builder different is that you can interact with the database directly.

You can simply **dispatch** your query
```php
$result = $query->select()->from('moons')
    ->dispatch();
```
Which will return a generic `Response`. Read [more about responses](responses.md).

----

Or, we recommend **get()** or **set()** for most cases.
```php
$result = $query->select()->from('moons')
    ->get(); // alias of set()
```
Which will return a Set (array of or single`Collection`). 
Read [more about responses](responses.md).

----

**all()** sets removes the limit and returns a Set
```php
$result = $query->select()->from('moons')
    ->all();
```
Read [more about responses](responses.md).

----

**one()** and **first()** sets the limit to `1` before dispatching
```php
$result = $query->select()->from('moons')
    ->one(); // or first();
```
Returns a single `Collection`. Read [more about responses](responses.md).

----

**path()** dispatches and returns a Path.
```php
$result = $query->select()->from('moons')
    ->path();
```
Read [more about responses](responses.md).

**Note: This is not implemented until traversals are finished**

----

**tree()** dispatches and returns a Tree.
```php
$result = $query->select()->from('moons')
    ->tree();
```
Read [more about responses](responses.md).

**Note: This is not implemented until traversals are finished**

----

**scalar()** dispatches and returns a single, scalar value.
```php
$result = $query->select('name')->from('moons')->where('id', 5)
    ->scalar();
    
echo $result; // 'Miranda'
```
Read [more about responses](responses.md).

----

Lastly, you can execute a **command()** of your own.
```php
$result = $query->command("SELECT FROM moons");
```

This sends the script directly to the driver.
It is up to you to know which language to send (cypher, gremlin, etc).
This returns a generic `Response`. Read [more about responses](responses.md).

#### Dispatching from Update, Drop, and Insert
If you **drop()** with an id, it will dispatch immediately.
```php
$query->drop(3); // executes drop
```

----


If you pass data to **insert()** it will dispatch immediately.
```php
$query->into('characters')->insert('name', 'Shepard Book');

// This does not fire immediately
$query->insert()->data('name', 'Simon')->into('characters');

// So you must dispatch it
$query->dispatch();
```

----

**update()** (for now), doesn't dispatch anything on its own.
You must
```php
$query
    ->update('battles') // target
    ->where('place', 'serenity valley')
    ->withData('outcome', 'loss')
    ->dispatch(); // fires query
```

### Api differences between Command and Query builders
@todo A list of all the api differences
