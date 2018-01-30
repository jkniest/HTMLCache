# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.0.4] - 2018-01-30
### Added
- Support for laravel 5.6 ([#13](https://github.com/jkniest/HTMLCache/pull/13))

## [1.0.3] - 2018-01-07
### Changed
- GET parameters are now considered in the cache generation
- Offical PHP 7.2 support

## [1.0.2] - 2017-11-21
### Changed
- Sites are not being cached if they do not have a 200 status code
- Sites are not being cached if there is a validation error

## [1.0.1] - 2017-09-03
### Added
- Support for laravel 5.5 (Auto package discovery)

## 1.0.0 - 2017-08-28
### Added
- First implementation of middleware
- Cache key generation based on page, language and user id
- Configuration file to ignore specific routes

[1.0.1]: https://github.com/jkniest/HTMLCache/compare/1.0.0...1.0.1
[1.0.2]: https://github.com/jkniest/HTMLCache/compare/1.0.1...1.0.2
[1.0.3]: https://github.com/jkniest/HTMLCache/compare/1.0.2...1.0.3
[1.0.4]: https://github.com/jkniest/HTMLCache/compare/1.0.3...1.0.4
