# v1.0 active-resource todo list

## Big Picture

* Add **IoC Container** w/ illuminate/container & resolve classes from there

* Perhaps add interface + **repository** + class for remote comms (guzzle)

* Refactor ActiveResource class using other classes / interfaces with **single-responsibility** (re: aa-41)

* Add **Facade** pattern to get rid of static properties / methods

* Add **illuminate/config** to have built-in config files vs base class requirements

* Add **illuminate/support** for ServiceProvider usage

* Unit Test everything

* Combine `purgeAttribute()` and `__unset()`

* Setup proper Travis CI



### Short term

* Remove 'ActiveResource' from the class names.  Change 'ActiveResource' to Model. Then the ActiveResource facade could return `ActiveResource\Model`

* Add some kind of `ConfigManager` maybe that gets settings for the `Model` and takes a `Container` instance in the constructor, w/ its own Facade and Service Provider setup

* Add a RequestManager facade + class + service provider that manages the Guzzle Request object. Perhaps this could even have methods for binding error handlers etc. And have an Interface associated.

* Add some kind of binary and task for setting up config?