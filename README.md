# OS2Web Data lookup  [![Build Status](https://travis-ci.org/OS2web/os2web_datalookup.svg?branch=8.x)](https://travis-ci.org/OS2web/os2web_datalookup)
## Install

OS2Web Data lookup provides integration with Danish data lookup services such as Service platformen or Datafordeler.
Module is available to download via composer.
```
composer require os2web/os2web_datalookup
drush en os2web_datalookup
```

## Update
Updating process for OS2Web Data lookup module is similar to usual Drupal 8 module.
Use Composer's built-in command for listing packages that have updates available:

```
composer outdated os2web/os2web_datalookup
```

## Automated testing and code quality
See [OS2Web testing and CI information](https://github.com/OS2Web/docs#testing-and-ci)

## Contribution

Project is opened for new features and os course bugfixes.
If you have any suggestion or you found a bug in project, you are very welcome
to create an issue in github repository issue tracker.
For issue description there is expected that you will provide clear and
sufficient information about your feature request or bug report.

### Code review policy
See [OS2Web code review policy](https://github.com/OS2Web/docs#code-review)

### Git name convention
See [OS2Web git name convention](https://github.com/OS2Web/docs#git-guideline)

### Using services in other modules

```
\Drupal::service('plugin.manager.os2web_datalookup')->createInstance('serviceplatformen_cvr')->getLegalUnit('[CVR number]')
\Drupal::service('plugin.manager.os2web_datalookup')->createInstance('serviceplatformen_cpr')->cprBasicInformation('[CPR number]'))
```

## New services/features

### Datafordeler integration (https://datafordeler.dk)

In scope of os2forms project already implemented light integration
with Danmarks Adresseregister (DAR) via fetching data for form elements
autocomplete. See [os2forms_dawa submodule](https://github.com/OS2Forms/os2forms)

As soon as it is clear how the integration is going to be used, then
os2forms_dawa will be refactored to OS2Web Data lookup plugin plugin.
