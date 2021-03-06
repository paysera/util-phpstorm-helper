# PhpStorm helper

[![Latest Version][ico-version]][link-releases]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

Scripts to set-up PhpStorm with shared configuration.

## Why?

Each of us like to customise our developer experience – shortcuts, layout and other tremendously important things like
color schemes. But some of the configuration is usually best to be shared with other team members:
* customized code style, inspections, generated code templates;
* directories configured by project's structure;
* correct SQL dialect;
* some plugins enabled for the project;
* PHP interpreter as docker with correct image;
* integration with any quality control tools;
* external tools available to run;
* configuration for servers to allow debugging;
* some common plugins to be installed.

This allows more consistency in your team and avoids configuring everything for each member separately.

There is `Settings repository` feature in PhpStorm, but it does not synchronize most of such things, and it does
synchronize some stuff that's supposed to be private (like color scheme).

There are also three different places where this configuration should be changed:
* in `.idea` folder – files, that can be versioned. This library helps to make initial configuration easier;
* in `.idea` folder, but in file, which should not be versioned. This requires script to be ran in each machine
to modify that file;
* in global PhpStorm configuration files – installed plugins and external tools. These are shared for all projects.

## Installation

Download latest release of `phpstorm-helper.phar` from [releases page](https://github.com/paysera/util-phpstorm-releases/releases).

Automated:
```
curl -sLo phpstorm-helper https://github.com/paysera/util-phpstorm-helper/releases/download/1.0.0/phpstorm-helper.phar
chmod +x phpstorm-helper
./phpstorm-helper self-update
sudo mv phpstorm-helper /usr/local/bin/phpstorm-helper
```

You will also need zip library to download and extract the plugins. To install on Ubuntu:
```bash
sudo apt-get install php-zip
```

## Usage

### TL;DR;

Once (better to run when PhpStorm is closed):
```bash
phpstorm-helper configure-installation
```

For each project:
- Close project
```bash
phpstorm-helper configure
```
- Reopen project

Or, with more options:
```bash
phpstorm-helper configure --docker-image php-cli:7.4 --webpack-config-path config/webpack.js --server project-url-address.docker:80@/project-path-inside/container
```

Following runs for the same project can skip the options unless you want to change any of those.

### Commands

As you already noted, there are 2 different use-cases:
* setting up (possibly) versioned configuration files in `.idea` and modifying `workspace.xml` 
(which cannot be versioned). Library provides the defaults used for repositories made by Paysera – feel free 
to use those or customise with your own;
* configuring external tools and installing common plugins, which is done in PhpStorm installation folder.

### Setting up versioned configuration files

```bash
phpstorm-helper configure [project-root-dir] [path-to-configuration-template-structure]
```

Features of default configuration:
* customized code style and generated code templates to be compatible with
[Paysera style guide](https://github.com/paysera/php-style-guide);
* customized inspections – some disabled, some optional ones enabled;
* MariaDB SQL dialect;
* Symfony plugin enabled for the project;
* service names for Symfony service generation follows the conventions;
* directories are marked as they should:
  * vendor directories not added as Git roots – no git update is called for libraries after Project update;
  * generated files are marked as excluded – you won't see duplicated find results;
  * configuration for vendors – vendors are excluded, but inside include path. This makes Scopes with following
  configuration:
    * `Project Files` (or `In Project`) – vendors are not included;
    * `Project and Libraries` – vendors are included. Choose `Scope → Project and Libraries` when searching;
* PHP interpreter as docker with correct image. This allows to run unit tests using PhpStorm integration in the right
environment;
* `php-cs-fixer` integration – if something's wrong, it will be marked as Weak Warning.

Some features require additional options or already existing files (like `.php_cs`) in current directory.
Run with `--help` for more information.

### Configuring external tools and installing plugins

```bash
phpstorm-helper configure-installation [path-to-config-file]
```

`path-to-config-file` is path to `php` configuration file, which has the following structure:
```php
<?php
declare(strict_types=1);

use Paysera\PhpStormHelper\Entity\ExternalToolConfiguration;
use Paysera\PhpStormHelper\Entity\GlobalConfiguration;

return (new GlobalConfiguration())

    ->setExternalToolConfigurations([
        (new ExternalToolConfiguration())
            ->setName('Fix CS (debug)')
            ->setCommand('bin/php-cs-fixer')
            ->setParameters('fix --diff --verbose --config=$ProjectFileDir$/.php_cs --dry-run "$FilePath$"')
            ->setSynchronizationRequired(false)
            ->setConsoleShownAlways(true),
    ])
    
    // copy direct links from plugin pages, for example https://plugins.jetbrains.com/plugin/7219-symfony-plugin
    // versions will get old, but prompt will appear to update the plugin
    // if plugin by that name already exists, no action will be taken
    ->setPlugins([
        'https://plugins.jetbrains.com/files/7219/59728/Symfony_Plugin-0.17.171.zip',
    ])
;

```

Alternatively, you can extend suggested configuration:
```
<?php
declare(strict_types=1);

use Paysera\PhpStormHelper\Entity\SuggestedGlobalConfiguration;
use Paysera\PhpStormHelper\Entity\ExternalToolConfiguration;

return (new SuggestedGlobalConfiguration())

    ->addExternalToolConfigurations([
        (new ExternalToolConfiguration())
            ->setName('PHP server')
            ->setCommand('php')
            ->setParameters('-S 0.0.0.0:8080'),
    ])
    
    ->addPlugins([
        'https://example.com/my-custom-plugin.jar',
    ])
;
```

If you don't provide config path, our suggested global configuration is used.

## Semantic versioning

This library follows [semantic versioning](http://semver.org/spec/v2.0.0.html).

See [Symfony BC rules](https://symfony.com/doc/current/contributing/code/bc.html) for basic information
about what can be changed and what not in the API. Keep in mind, that in this bundle everything is
`@internal` by default – only use the classes and methods marked with `@api` directly.

Any other files, including default configuration for the project, can also change with any release.

Default PhpStorm configuration, tools or plugins can change with any version number.

## Running tests

```
composer update
composer test
```

## Contributing

Feel free to create issues and give pull requests.

You can fix any code style issues using this command:
```
composer fix-cs
```

[ico-version]: https://img.shields.io/github/v/release/paysera/util-phpstorm-helper?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/paysera/util-phpstorm-helper/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/paysera/util-phpstorm-helper.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/paysera/util-phpstorm-helper.svg?style=flat-square

[link-releases]: https://github.com/paysera/util-phpstorm-helper/releases
[link-travis]: https://travis-ci.org/paysera/util-phpstorm-helper
[link-scrutinizer]: https://scrutinizer-ci.com/g/paysera/util-phpstorm-helper/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/paysera/util-phpstorm-helper
