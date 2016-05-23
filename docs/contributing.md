# Contributing
Contributions are **welcome** and will be fully **credited**.
We are excited about anything from the simplest bug squash to a new driver to a new feature.

We accept contributions via Pull Requests on [Github](https://github.com/spider/spider).

Please look at the [roadmap](roadmap.md) and [internals](internals.md) to get a feel for how everything works.

## Pull Requests
- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).
- **Add tests!** - Your patch won't be accepted if it doesn't have tests or breaks existing tests (without discussion).
- **Document any change in behaviour** - Make sure the relevant documentation are kept up-to-date.
- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.
- **Create feature branches** - Don't ask us to pull from your master branch.
- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.
- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

## Branches
The **master** branch always contains the most up-to-date, production ready release. In most cases, this will be the same as the latest release under the "releases" tab.

the **develop** branch holds work in progress for the next release. Any work here should be stable. The idea is that security patches, refactors, and new features are merged into this branch. Once enough patches has been tested here, it will be merged into `master` and released. This branch should always be stable.

**feature-** branches hold in progress work for upcoming features destined for future major or minor releases. These can be unstable.

**patch-** branches hold in progress patches for upcoming point releases, security patches, and refactors. These can be unstable.

Be sure to fetch often so you keep your sources up-to-date!

## Test Databases and Development Environment
In order to really test Spider, you need to install the test databases for the different drivers.
At the moment, there are three drivers. 

You have three options in setting up your development environment. Instructions are provided for installing each database individually, you may use the provided `spider-dev` utility (with docker), or a [Vagrant](http://vagrantup.com/) box is provided to use a pre-configured virtual machine.

### Spider-Dev command line utility
NOTE: For the moment, this utility is not totally compatible with Windows or Mac versions of docker that do not run native.
You may use the second option (vagrant) on these environments, or use the new new native docker apps (http://beta.docker.com)

It is easy to setup a development environment to test Spider by using the `spider-dev` command line utility that ships with Spider. It also includes
configured boxes for several versions of all the database systems, and all supported versions of php and hhvm.

Spider-Dev uses Docker to create and maintain docker containers that are supported by Spider.

Basic commands include: (note you must run it from the spider directory where composer.json lives)
```
cd /path/to/spider
bin/spider-dev up # Starts up databases
bin/spider-dev down # Shuts down databases
bin/spider-dev test # Runs all the tests
bin/spider-dev shell # Starts all the services, and opens a shell instance on the php container so you can play around
```

For more information (including options about choosing which versions and tests you run):

```
bin/spider-dev help
```

### Spider Vagrant Box
See https://github.com/spider/spider-box for full instructions. It's easy, we promise :)

### Install locally
You may also setup the databases locally. These are instructions for installing each db so all tests will pass.

#### Gremlin-Server
##### Download
- [Gremlin-server](https://www.apache.org/dist/incubator/tinkerpop/3.0.0-incubating/apache-gremlin-server-3.0.0-incubating-bin.zip)
- [gremlin-server-php.yaml](https://raw.githubusercontent.com/PommeVerte/gremlin-php/master/src/tests/gremlin-server-php.yaml)
- [gremlin-php-script.groovy](https://raw.githubusercontent.com/PommeVerte/gremlin-php/master/src/tests/gremlin-php-script.groovy)
- [neo4j-empty.properties](https://raw.githubusercontent.com/PommeVerte/gremlin-php/master/src/tests/neo4j-empty.properties)

##### Installation
Extract Gremlin-server and go into the created folder.

Neo4J is required for the full test suit (it serves as the transaction enabled graph). It is not bundled with gremlin-server by default so you will need to manually install it with:

```bash
bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin 3.0.0-incubating
```
Copy the following files :

```bash
cp path/to/gremlin-server-php.yaml <gremlin-server-root-dir>/conf/
cp path/to/neo4j-empty.properties <gremlin-server-root-dir>/conf/
cp path/to/gremlin-php-script.groovy <gremlin-server-root-dir>/scripts/
```

You will then need to run gremlin-server in the following manner (it's important to run it from the `<gremlin-server-root-dir>`):

```bash
bin/gremlin-server.sh conf/gremlin-server-php.yaml
```

Note that if you are using Windows, you have to reverse the directory seperators in all those paths
and use .bat files:
```bash
bin\gremlin-server.bat conf\gremlin-server-php.yaml
```

#### Neo4J
##### Download
- [Neo4J Server](http://neo4j.com/download/) (Community version is tested)

##### Installation
Once the server is up and running, connect to [http://localhost:7474/browser](http://localhost:7474/browser) 
- Change the username:password from `neo4j:neo4j` to `neo4j:j4oen`
- It is recommended to create a 'testing' graph or similar, as the test suite will wipe all existing data before and after each test.

#### OrientDB
##### Download
- [OrientDB Community](http://orientdb.com/download/) (Community version is tested)

##### Installation
Simply extract the directory and place it anywhere you like.

That's really it. The server can be accessed by connecting to : [http://localhost:2480/](http://localhost:2480/).

You should be good to go.

All tests use fixtures which provide a clean slate for testing. For OrientDB, nothing extra is needed
(it will create and destroy a database).
For neo, it is recommended to create a testing graph.

**Happy coding**!
