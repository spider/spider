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
public $languages = [];
public function open();
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

## Step Two: Define supported languages
You can define which query languages are supported by your driver by defining the `$languages` property:
```php
public $languages = [
   'cypher' => '\namespace\to\cypher\Processor',
   'gremlin' => '\namespace\to\gremlin\Processor',
];
```
Read more about [language processors](#the-language-processor).

## Step Three: Implement Database Connections
This is pretty simple. 
In most cases, your driver will be extending some other language binding tool.

The `open()` method should open a database connection and `close` should close it.

## Step Four: Implement Read and Write Commands
Read and write commands are treated differently by some databases, so they are divided here.
Each method takes a `Spider\Commands\Command` which has two methods:
  * getScript() to return the script to be executed
  * getScriptLanguage() to return the language the script is written in to check that it is compatible with the driver

Or a `Spider\Commands\BaseBuilder` that will need to be converted to a `Spider\Commands\Command`. `Spider\Drivers\AbstractDriver` provides some methods to help simplify this process. Namely:
  * isSupportedLanguage(string) will tell you if string is a supported language for this driver.

Bellow is an example of it in use:

```php
if ($command instanceof \Spider\Commands\BaseBuilder) {
    $processor = new $this->languages['cypher'];
    $command = $command->getCommand($processor);
} elseif (!$this->isSupportedLanguage($command->getScriptLanguage())) {
    throw new NotSupportedException(__CLASS__ . " does not support ". $command->getScriptLanguage());
}
```

##### Return values
The `executeReadCommand()` and `executeWriteCommand()` methods must return a `Spider\Drivers\Response`
```php
$response = // do whatever to get results from database
return new Response(['_raw' => $response, '_driver' => $this]);
```

The `runReadCommand()` and `runWriteCommand()` methods are identical, except they don't return anything.

## Step Five: Implement Transactions
Implement transactions with `startTransaction()` and `stopTransaction(bool $commit)`.
This will vary wildly for each database. Check out documentation and look through the first-party drivers for ideas.

Note that `stopTransaction()` should commit by default, unless `false` is passed it.

Also note that when a driver is `__destruct()`ed, `stopTransaction()` is called. As is `close()`d

## Step Six: Implement Response Formatting
Honestly, this may be the trickiest part.

The driver must always return a `Spider\Drivers\Response` when executing a command.
The Response simply holds the raw results from the database and the driver itself.
The raw results can be anything from a simple array to a complicated type-hinting object.
What matters is that the driver understands what to do with it.

So, when one of the `formatAs` methods is called, it should map the response to the correct format.
See [response formats](responses.md) for more info about that.

Take a look at the current drivers to see how these were implemented.

## Step Seven: Share the Love
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

## Driver and Language Processor Testing
To make sure each language processor and driver behave in predictable ways, there are Base Test Suites that hold all the tests.
In order to make your driver and processor compliant, create tests that extend these tests. The driver and processor are not considered stable until all these tests pass.

### Driver Testing
  1. Create a test that extends `Spider\Test\Unit\Drivers\BaseTestSuite`.
  1. On `setup()` and `teardown`, manage fixtures, if you have them.
  
  ```php
  public function setup()
      {
          $this->fixture = new OrientFixture();
          $this->fixture->unload();
          $this->fixture->load();
      }
  
      public function teardown()
      {
          $this->fixture->unload();
      }
  ```
  
  1. Implement `driver()` which returns the configured driver instance
  1. Implement `getMetaKey()` which returns a string of a meta key your driver uses. Something that your driver will have. For Orient, as an example, this is '@rid`.
  1. Implement `getScalarResponse()` which returns a response that can be `formatAsScalar()`
  1. Implement the rest of the methods, which return `Command()` objects for the desired operation. This is all documented.
  1. Add any vendor-specific tests.
  
Now, if your tests pass, the driver is up to spec.

### Language Processor Testing
  1. Create a test that extends `Spider\Test\Unit\Commands\Languages\BaseTestSuite`.
  1. Implement `processor()` which returns the new processor instance
  1. Implement the rest of the methods, which return `Command()` objects for the desired operation. This is all documented.
  1. Add any vendor-specific tests.
  
Now, if your tests pass, the processor is up to spec.