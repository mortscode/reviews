# Reviews Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.0.10 - 2021-04-01

### Fixed

- Fixed issue where a dump & dive was left in the controller

## 1.0.9 - 2021-04-01

### Fixed

- Fixed issue where recaptcha secret wasn't being parsed correctly

## 1.0.8 - 2021-03-25

### Fixed

-   Solved issue where plugin `getRecaptchaKey()` variable was expecting a model that had been depreciated

## 1.0.7 - 2021-03-25

### Fixed

-   Moved recaptcha env var template function into services
-   Wrapped env var in `parseEnv()` method

## 1.0.6 - 2021-03-25

### Added

-   ReCaptcha keys can be stored as .env vars

## 1.0.5 - 2021-01-25

### Added

-   Disqus XML importer

## 1.0.4 - 2021-01-04

### Added

-   ReCaptcha validation on form submission

## 1.0.3.1 - 2020-12-14

### Fixed

-   Issue where form would re-submit on page refresh

## 1.0.3 - 2020-12-14

### Added

-   Form validations
-   DevMode record delete
-   Only shows "Approved" reviews

### Added

-   README Updates

### Fixed

-   Depreciation warning in settings template

## 1.0.1 - 2020-11-24

### Added

-   Settings: Reviews Sections
-   Settings: Main Column Title

### Changed

-   Settings: Reviews Sections now in CP

## 1.0.0 - 2020-10-23

### Added

-   Initial release
