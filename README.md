# OS2Web Data lookup  [![Build Status](https://travis-ci.org/OS2web/os2web_datalookup.svg?branch=8.x)](https://travis-ci.org/OS2web/os2web_datalookup)

## Install

OS2Web Data lookup provides integration with Danish data lookup services such as
Service platformen or Datafordeler. Module is available to download via
composer.

```shell
composer require os2web/os2web_datalookup
drush en os2web_datalookup
```

## Update

Updating process for OS2Web Data lookup module is similar to the usual Drupal 8
module. Use Composer's built-in command for listing packages that have updates
available:

```shell
composer outdated os2web/os2web_datalookup
```

## Automated testing and code quality

See [OS2Web testing and CI information](https://github.com/OS2Web/docs#testing-and-ci)

## Contribution

Project is opened for new features and os course bugfixes.
If you have any suggestion, or you found a bug in project, you are very welcome
to create an issue in GitHub repository issue tracker. For issue description,
there is expected that you will provide clear and sufficient information about
your feature request or bug report.

### Code review policy

See [OS2Web code review policy](https://github.com/OS2Web/docs#code-review)

### Git name convention

See [OS2Web git name convention](https://github.com/OS2Web/docs#git-guideline)

### Using services in other modules

```php
// CVR lookup
/** @var \Drupal\os2web_datalookup\Plugin\DataLookupManager $pluginManager */
$pluginManager = \Drupal::service('plugin.manager.os2web_datalookup');
/** @var \Drupal\os2web_datalookup\Plugin\os2web\DataLookup\DataLookupCompanyInterface $cvrPlugin */
$cvrPlugin = $pluginManager->createDefaultInstanceByGroup('cvr_lookup');

if ($cvrPlugin->isReady()) {
  $cvrResult = $cvrPlugin->lookup($cvr);
}

// CPR lookup.
/** @var \Drupal\os2web_datalookup\Plugin\DataLookupManager $pluginManager */
$pluginManager = \Drupal::service('plugin.manager.os2web_datalookup');
/** @var \Drupal\os2web_datalookup\Plugin\os2web\DataLookup\DataLookupCprInterface $cprPlugin */
$cprPlugin = $pluginManager->createDefaultInstanceByGroup('cpr_lookup');

if ($cprPlugin->isReady()) {
  $cprResult = $cprPlugin->lookup($cpr);
}

// CVR lookup - DEPRECATED.
\Drupal::service('plugin.manager.os2web_datalookup')->createInstance('serviceplatformen_cvr')->getLegalUnit('[CVR number]')


// CPP lookup - DEPRECATED.
\Drupal::service('plugin.manager.os2web_datalookup')->createInstance('serviceplatformen_cpr')->cprBasicInformation('[CPR number]'))
```

## New services/features

### Datafordeler integration (<https://datafordeler.dk>)

In the scope of os2forms project already implemented light integration with
Danmarks Adresseregister (DAR) via fetching data for form elements autocomplete.

See [os2forms_dawa submodule](https://github.com/OS2Forms/os2forms)

As soon as it is clear how the integration is going to be used, then
os2forms_dawa will be refactored to OS2Web Data lookup plugin.

## Important notes

### Serviceplatformen plugins

Settings for CPR and CVR serviceplatformen plugins are storing as configuration
in db and will(could) be exported as `yml` file via Drupal's configuration
management system. And afterward could be tracked by `git`.

If case you have public access to your git repository, all settings from plugins
will be exposed for third persons.

To avoid/prevent this behavior, we recommend use `Config ignore` module, where
you can add all settings you do not want to export/import via the configuration
management system.

## Coding standards

Our coding are checked by GitHub Actions (cf.
[.github/workflows/pr.yml](.github/workflows/pr.yml)). Use the commands below to
run the checks locally.

### PHP

```shell
docker run --rm --volume ${PWD}:/app --workdir /app itkdev/php8.1-fpm composer install
# Fix (some) coding standards issues
docker run --rm --volume ${PWD}:/app --workdir /app itkdev/php8.1-fpm composer coding-standards-apply
# Check that code adheres to the coding standards
docker run --rm --volume ${PWD}:/app --workdir /app itkdev/php8.1-fpm composer coding-standards-check
```

### Markdown

```shell
docker run --rm --volume $PWD:/md peterdavehello/markdownlint markdownlint --ignore vendor --ignore LICENSE.md '**/*.md' --fix
docker run --rm --volume $PWD:/md peterdavehello/markdownlint markdownlint --ignore vendor --ignore LICENSE.md '**/*.md'
```

## Code analysis

We use [PHPStan](https://phpstan.org/) for static code analysis.

Running statis code analysis on a standalone Drupal module is a bit tricky, so we use a helper script to run the
analysis:

```shell
docker run --rm --volume ${PWD}:/app --workdir /app itkdev/php8.1-fpm ./scripts/code-analysis
```
