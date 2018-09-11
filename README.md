# WORK IN PROGRESS

# LaravelElasticsearchMigration

[![Software license][ico-license]](LICENSE)
[![Travis][ico-travis]][link-travis]
[![Coveralls](https://coveralls.io/repos/github/triadev/LaravelElasticsearchMigration/badge.svg?branch=master)](https://coveralls.io/github/triadev/LaravelElasticsearchMigration?branch=master)
[![CodeCov](https://codecov.io/gh/triadev/LaravelElasticsearchMigration/branch/master/graph/badge.svg)](https://codecov.io/gh/triadev/LaravelElasticsearchMigration)
[![Latest stable][ico-version-stable]][link-packagist]
[![Latest development][ico-version-dev]][link-packagist]
[![Monthly installs][ico-downloads-monthly]][link-downloads]

Elasticsearch migration for laravel.

## Main features
- Create index
- Update mappings
- Update settings
- Close/Open index for settings update (analyzer, ...)

## Installation

### Composer
> composer require triadev/laravel-elasticsearch-migration

### Application
The package is registered through the package discovery of laravel and Composer.
>https://laravel.com/docs/5.6/packages

## Configuration
| Key        | ENV        | Value           | Description  |
|:-------------:|-------------:|:-------------:|:-----:|
| host | ELASTICSEARCH_HOST | STRING | Host |
| port | ELASTICSEARCH_PORT | INTEGER | Default: 9200 |
| scheme | ELASTICSEARCH_SCHEME | STRING | https or http |
| user | ELASTICSEARCH_USER | STRING | Username |
| pass | ELASTICSEARCH_PASS | STRING | Password |
| migration.filePath | --- | STRING | File path for migration scripts |

### migrations.php
| Key        | Value           | Description  |
|:-------------:|:-------------:|:-----:|
| *.index  | STRING | Index |
| *.type  | STRING (create, update or delete) | Type of migration |
| *.mappings  | ARRAY (create = required, optional) | Example: mappings |
| *.settings  | ARRAY (optional) | Example: settings |
| *.closeIndex  | BOOL (default = false) | Close index for settings update |

#### Example: mappings
```
'mappings' => [
    'phpunit' => [
        'dynamic' => 'strict',
        'properties' => [
            'title' => [
                'type' => 'text'
            ]
        ]
    ]
 ]
```

#### Example: settings
```
'settings' => [
    'index' => [
        'refresh_interval' => "60s"
    ]
]
```

## Roadmap
- delete index
- create/delete alias
- create/delete templates
- reindex

### Cli
- Start migration

## Reporting Issues
If you do find an issue, please feel free to report it with GitHub's bug tracker for this project.

Alternatively, fork the project and make a pull request. :)

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
