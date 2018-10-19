# LaravelElasticsearchMigration

[![Software license][ico-license]](LICENSE)
[![Travis][ico-travis]][link-travis]
[![Coveralls](https://coveralls.io/repos/github/triadev/LaravelElasticsearchMigration/badge.svg?branch=master)](https://coveralls.io/github/triadev/LaravelElasticsearchMigration?branch=master)
[![CodeCov](https://codecov.io/gh/triadev/LaravelElasticsearchMigration/branch/master/graph/badge.svg)](https://codecov.io/gh/triadev/LaravelElasticsearchMigration)
[![Latest stable][ico-version-stable]][link-packagist]
[![Latest development][ico-version-dev]][link-packagist]
[![Monthly installs][ico-downloads-monthly]][link-downloads]

Elasticsearch migration for laravel.

## Supported laravel versions
[![Laravel 5.5][icon-l55]][link-laravel]
[![Laravel 5.6][icon-l56]][link-laravel]
[![Laravel 5.7][icon-l57]][link-laravel]

## Supported elasticsearch versions
[![Elasticsearch 6.0][icon-e60]][link-elasticsearch]
[![Elasticsearch 6.1][icon-e61]][link-elasticsearch]
[![Elasticsearch 6.2][icon-e62]][link-elasticsearch]
[![Elasticsearch 6.3][icon-e63]][link-elasticsearch]
[![Elasticsearch 6.4][icon-e64]][link-elasticsearch]

## Main features
- Create index
- Update mappings
- Update settings
- Close/Open index for settings update (analyzer, ...)
- Create/Delete alias
- Reindex index
- Delete By Query
- Update by Query

## Installation

### Composer
> composer require triadev/laravel-elasticsearch-migration

### Application
The package is registered through the package discovery of laravel and Composer.
>https://laravel.com/docs/5.6/packages

### Events
[Documentation: Laravel Events](https://laravel.com/docs/5.7/events)

Namespace: Triadev\EsMigration\Business\Events

#### Migration
| Event        | Status  |
|:-------------:|:-----:|
| MigrationRunning | Migration is running |
| MigrationError | Migration is failed |
| MigrationDone | Migration is done |

#### Migration - Step
| Event        | Status  |
|:-------------:|:-----:|
| MigrationStepRunning | Migration step is running |
| MigrationStepError | Migration step is failed |
| MigrationStepDone | Migration step is done |

## Roadmap
- create/delete templates
- shrink index
- split index
- rollover index

## Reporting Issues
If you do find an issue, please feel free to report it with GitHub's bug tracker for this project.

Alternatively, fork the project and make a pull request. :)

## Testing
1. docker-compose -f docker-compose.yml up
2. composer test

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits
- [Christopher Lorke][link-author]
- [All Contributors][link-contributors]

## Other

### Project related links
- [Wiki](https://github.com/triadev/LaravelElasticsearchMigration/wiki)
- [Issue tracker](https://github.com/triadev/LaravelElasticsearchMigration/issues)

### License
The code for LaravelElasticsearchMigration is distributed under the terms of the MIT license (see [LICENSE](LICENSE)).

[ico-license]: https://img.shields.io/github/license/triadev/LaravelElasticsearchMigration.svg?style=flat-square
[ico-version-stable]: https://img.shields.io/packagist/v/triadev/laravel-elasticsearch-migration.svg?style=flat-square
[ico-version-dev]: https://img.shields.io/packagist/vpre/triadev/laravel-elasticsearch-migration.svg?style=flat-square
[ico-downloads-monthly]: https://img.shields.io/packagist/dm/triadev/laravel-elasticsearch-migration.svg?style=flat-square
[ico-travis]: https://travis-ci.org/triadev/LaravelElasticsearchMigration.svg?branch=master

[link-packagist]: https://packagist.org/packages/triadev/laravel-elasticsearch-migration
[link-downloads]: https://packagist.org/packages/triadev/laravel-elasticsearch-migration/stats
[link-travis]: https://travis-ci.org/triadev/LaravelElasticsearchMigration

[icon-l55]: https://img.shields.io/badge/Laravel-5.5-brightgreen.svg?style=flat-square
[icon-l56]: https://img.shields.io/badge/Laravel-5.6-brightgreen.svg?style=flat-square
[icon-l57]: https://img.shields.io/badge/Laravel-5.7-brightgreen.svg?style=flat-square

[icon-e60]: https://img.shields.io/badge/Elasticsearch-6.0-brightgreen.svg?style=flat-square
[icon-e61]: https://img.shields.io/badge/Elasticsearch-6.1-brightgreen.svg?style=flat-square
[icon-e62]: https://img.shields.io/badge/Elasticsearch-6.2-brightgreen.svg?style=flat-square
[icon-e63]: https://img.shields.io/badge/Elasticsearch-6.3-brightgreen.svg?style=flat-square
[icon-e64]: https://img.shields.io/badge/Elasticsearch-6.4-brightgreen.svg?style=flat-square

[link-laravel]: https://laravel.com
[link-elasticsearch]: https://www.elastic.co/
[link-author]: https://github.com/triadev
[link-contributors]: ../../contributors
