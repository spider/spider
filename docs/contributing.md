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

### RunningTests
```bash
phpunit
```
Note that the Driver tests are disabled for the auto-build Travis CI process.
They require a working database.
Instructions for setting up fixtures are on the way.

## Branches
The **master** branch always contains the most up-to-date, production ready release. In most cases, this will be the same as the latest release under the "releases" tab.

the **develop** branch holds work in progress for the next release. Any work here should be stable. The idea is that security patches, refactors, and new features are merged into this branch. Once enough patches has been tested here, it will be merged into `master` and released. This branch should always be stable.

**feature-** branches hold in progress work for upcoming features destined for future major or minor releases. These can be unstable.

**patch-** branches hold in progress patches for upcoming point releases, security patches, and refactors. These can be unstable.

Be sure to fetch often so you keep your sources up-to-date!

**Happy coding**!
