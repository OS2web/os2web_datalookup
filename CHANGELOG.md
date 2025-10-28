# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

* Fixing incorrect Datafordeler local certificate usage.

## [3.0.1] - 2025-06-18

* [PR-26](https://github.com/OS2web/os2web_datalookup/pull/26)
  Revert "Made DataLookupBase::getCertificate abstract"

* Making getCertificate non-abstract.

## [3.0.0] - 2025-06-18

* [PR-13](https://github.com/OS2web/os2web_datalookup/pull/13)
  Added support for [os2web_key](https://github.com/OS2web/os2web_key)

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

[Unreleased]: https://github.com/os2web/os2web_datalookup/compare/3.0.0...HEAD
[3.0.0]: https://github.com/os2web/os2web_datalookup/compare/2.0.4...3.0.0
[2.0.4]: https://github.com/os2web/os2web_datalookup/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/os2web/os2web_datalookup/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/os2web/os2web_datalookup/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/os2web/os2web_datalookup/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/os2web/os2web_datalookup/compare/1.11.5...2.0.0
