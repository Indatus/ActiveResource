### Changelog

### 0.2.0

- Refactored many static methods and properties to make better use of the **Facade Pattern**
- Refactored service provider to bind package and dependent core classes
- Added Config settings management

### 0.1.6

- Added HTTP 500 response support

### 0.1.5

- Updated to `illuminate/support` 4.x

### 0.1.4

- Updated documentation within code

### 0.1.3

- Added `newInstance` method as a convenience

### 0.1.2

- Added `purgeAttribute` method to unset an entity property

### 0.1.1

- Added ability to make requests without an entity via `rawGet` `rawPut` `rawPost` `rawPatch` and `rawDelete`
- Added `ActiveResourceRawResponse` object to get returned by raw requests

### 0.1.0

- Initial release
