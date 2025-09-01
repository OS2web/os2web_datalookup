# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.5] 2025-09-01

* [PR-22](https://github.com/OS2web/os2web_datalookup/pull/22)
  Set actual identifier from lookup on lookup result

## [2.0.4] 2025-01-29

* Ensure postal code is only added to city if `CVRAdresse_postdistrikt` is not set.
* Added missing use statement to fix issue on datafordeler settings pages
  `pnumber_lookup`, `cvr_lookup` and `cpr_lookup`.

## [2.0.3] 2025-01-24

* Fixing warning if foedselsdato not set.

## [2.0.2] 2024-12-06

* Avoided accessing properties being they are initialized.

## [2.0.1] 2024-11-22

* Updated audit logging messages in Serviceplatformen services.

## [2.0.0] 2024-11-21

* Audit logging.

[Unreleased]: https://github.com/os2web/os2web_datalookup/compare/2.0.5...HEAD
[2.0.5]: https://github.com/os2web/os2web_datalookup/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/os2web/os2web_datalookup/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/os2web/os2web_datalookup/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/os2web/os2web_datalookup/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/os2web/os2web_datalookup/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/os2web/os2web_datalookup/compare/1.11.5...2.0.0
