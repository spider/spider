# Creating a Driver
Spider comes with three drivers: Gremlin Server, OrientDB, and Neo4j.

In most cases, one of these should be all you need to work with Graph Databases.
Orient and Neo4j are the most popular in the php world and Gremlin Server is compatible with almost all graph databases.

However, you may create your own driver to connect to any datastore.
Note that you don't *have* to use Spider with graph databases.
There are plans to create a MySql and Mongo driver.

## Before we Begin
Take a look at Spider's test suites. You will unit tests for each supported driver (tests/Unit/Drivers).
You will also notice that each of these Driver tests extend `BaseTestSuite` which includes several contract methods.
The best way to build a driver of your own is to create a test that extends this `BaseTestSuite`, implement the required methods,
and let the tests fly. This way you know your driver is capable of everything a first-party driver can do.

## Step One: Setup
The only thing you need to do to setup is create a new class that extends
`Spider\Drivers\AbstractDriver` which will also implements
`Spider\Drivers\DriverInterface`

Next, create a constructor that accepts an array of config properties (whatever you need to connect).
The best thing to do is simply create protected properties for each of these config items, and then call the parent constructor to set them automatically.

The constructor should also setup any dependencies you may need.

Here's an example from OrientDB
```php
public function __construct(array $properties = [])
{
    // Populate configuration
    parent::__construct($properties);

    // Initialize the language binding client
    $this->client = new PhpOrient();
}
```
I
The DriverInterface can be broken into three parts.
The first **manages database connections** and utilities:
```php
public function makeProcessor(); // Returns the language processor of choice
public function open(); // 
public function close();
```

The second part **executes commands**
```php
public function executeReadCommand(CommandInterface $query);
public function executeWriteCommand(CommandInterface $command);
public function runReadCommand(CommandInterface $query);
public function runWriteCommand(CommandInterface $command);
public function startTransaction();
public function stopTransaction($commit = true);
```

The last part **handles response formatting**
```php
public function formatAsSet($response);
public function formatAsTree($response);
public function formatAsPath($response);
public function formatAsScalar($response);
```

Let's tackle each piece one at a time.

## Step Two: Implement Database Connections
This is pretty simple. In most cases, your driver will be extending some other language binding tool.

The `makeProcessor()` should return the preferred [language processor](#language-processor).

The `open()` method should open a database connection and `close` should close it.

## Step Three: Implement Read and Write Commands
Read and write commands are treated differently by some databases, so they are divided here.
Each method takes a `Spider\Commands\Command` which has three methods:
  * getScript() to return the script to be executed
  * getScriptLanguage() to return the language the script is written in
  * getRw() to return whether this is a read or write command

The `executeReadCommand()` and `executeWriteCommand()` methods must return a `Spider\Drivers\Response`
```php
$response = // do whatever to get results from database
return new Response(['_raw' => $response, '_driver' => $this]);
```

The `runReadCommand()` and `runWriteCommand()` methods are identical, except they don't return anything.

## Step Four: Implement Transactions
Implement transactions with `startTransaction()` and `stopTransaction(bool $commit)`.
This will vary wildly for each database. Check out documentation and look through the first-party drivers for ideas.

Note that `stopTransaction()` should commit by default, unless `false` is passed it.

Also note that when a driver is `__destruct()`ed, `stopTransaction()` is called. As is `close()`

## Step Five: Implement Response Formatting
Honestly, this may be the trickiest part.

The driver must always return a `Spider\Drivers\Response` when executing a command.
The Response simply holds the raw results from the database and the driver itself.
The raw results can be anything from a simple array to a complicated type-hinting object.
What matters is that the driver understands what to do with it.

So, when one of the `formatAs` methods is called, it should map the response to the correct format.
See [response formats](responses.md) for more info about that.

Take a look at the current drivers to see how these were implemented.

## Step Six: Share the Love
Now that you have a lovely driver, please consider sharing it.
Open an issue at http://github.com/spider/spider to let us know about it. We won't steal it, we promise.

## The Language Processor
The Language Processor must implement the `ProcessorInterface` which only has one method: `process(Bag $bag)`

The idea is simple: turn the `Bag` into a `Command` with a script.

The implementation may be more difficult.
  1. Look carefully at `Spider\Commands\Bag`. It is well documented, so you know exactly what to receive.
  2. Look at the existing language processors for best-practices in processing a Bag.
  3. Look at the unit tests, they tell a lot.
  4. Don't forget to set the language and rw on the new Command.
