# Responses

When querying databases with Spider drivers you can expect the `Driver` class to return a `Spider\Drivers\Response` object:

```php
$driver = new \Spider\Drivers\Gremlin\Driver();
$driver->open();
$response = $driver->executeReadCommand($someQuery);
$response instanceof \Spider\Drivers\Response; // TRUE
```

### Converting the Response to a specific format

Due to the nature of graph databases and some of the querying languages available, it is not always possible to programmatically know what the expected output is. Because of this, `Response` comes with several formatting features you can use depending on the output you are exepecting.


##### Format as Set

A `Set` is the most frequent output format people use. It is essentially an array of graph elements such as Vertices or Edges.
If this is the type of response you are exepecting, getting the set is as simple as:

```php
$response->getSet();
```

If the database response only contains a single entry, then you will receive a single `Spider\Base\Collection`. If on the other hand the database returns multiple entries then you will get the following array:

```php
[
    Collection,
    Collection,
    Collection,
    //...
]
```

These `Collection` entries allow you to do the following:

```php

$collection = $response->getSet(); //lets say the db returned a single vertex

//Get any property
$collection->someProperty;

//Get any DB specific meta information
$collection->meta()->id;
$collection->meta()->label;
$collection->meta()->cluster;
$collection->meta()->type;

//Available shortcuts
$collection->id;
$collection->label;
```

*Note that `id`, `label` and `meta` are read-only*

##### Format as Path

Paths are ordered `Set`s representing the elements that a traversal query has walked in the order it has walked them. A single path query can return multiple paths because branching in the traversal may occure. If this is the format you are exepecting you can get a path formatted array with :

```php
$response->getPath();
```

This will return an array formatted as follows:

```php
[
    [Collection, Collection, Collection, ...] // each line is a path
    [Collection, Collection, Collection, ...]
    [Collection, Collection, Collection, ...]
    //...
]
```

##### Format as Scalar

Sometimes you query the database for a single Int, String or other. It can be anything from a Vertex name to a count of elements. When this is the result you are expecting you can get the scalar value with:

```php
$response->getScalar();
```
This will return which ever item you queried (Int, String, Bool, etc..)

##### Get the raw DB response

In some occasions you may want to retrieve the raw database data to use with an existing library. When this is the case you can get it with:


```php
$response->getRaw();
```

*Note that this will not be consistent accross `Driver` implementations. It is very `Driver` specific*


### Formatting Exceptions

If you ever try to convert a `Response` to an incorrect format. Like for example you queried a set of vertices and use `$response->getScalar()`; you will receive a `FormattingException`.
