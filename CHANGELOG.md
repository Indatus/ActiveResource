### Changelog

### 1.0.0
- **Significant updates with non-backwards-compatible changes**
- Refactored many static methods and properties to make better use of the _Facade Pattern_
- Refactored service provider to bind package and dependent core classes
- Added Config settings management
- Added unit tests
- Added dependencies on `illuminate/config ~4` and `iluminate/container ~4`
- Added dependency on forked `illuminate/filesystem` [found here](https://github.com/brianwebb01/filesystem)

### 0.1.8

- Added `__unset()` method for unsetting an instance property: _Thanks Troy Harvey_

### 0.1.7

- Formatting updates to documentation: _Thanks Jason McCreary_

### 0.1.6

- Added HTTP 500 response support handling or `find()` method: _Thanks Charles Griffin_

### 0.1.5

- Updated to `illuminate/support` 4.x

### 0.1.4

- Updated documentation within code

### 0.1.3

- Added `newInstance()` method as a convenience

### 0.1.2

- Added `purgeAttribute()` method to unset an entity property

### 0.1.1

- Added ability to make requests without an entity via `rawGet()` `rawPut()` `rawPost()` `rawPatch()` and `rawDelete()`
- Added `ActiveResourceRawResponse` object to get returned by raw requests

### 0.1.0

- Initial release
