# PhpClosure

[![Build Status](https://secure.travis-ci.org/loginwashere/PhpClosure.png?branch=master)](https://travis-ci.org/loginwashere/PhpClosure)

This is a fork of [php-closure](http://code.google.com/p/php-closure/) class
for working with google closure compiler api that I found on Google Code

## Documentation
Will be available on [phpclosure.readthedocs.org](https://phpclosure.readthedocs.org/en/latest/)

## Usage

```php
$c = new PhpClosure();
$c->add("path/to/js/my-app.js") // File must exist and be readable
    ->add("path/to/js/popup.js") // File must exist and be readable
    ->advancedMode()
    ->useClosureLibrary()
    ->cacheDir("path/to/tmp/js-cache/") // Directory must exist and be writable
    ->write();
```

or

```php
PhpClosure::create()
    ->add("path/to/js/my-app.js") // File must exist and be readable
    ->add("path/to/js/popup.js") // File must exist and be readable
    ->advancedMode()
    ->useClosureLibrary()
    ->cacheDir("path/to/tmp/js-cache/") // Directory must exist and be writable
    ->write();
```

For compiker options description go to [official documentation](https://developers.google.com/closure/compiler/docs/overview)

### Todo

* Add tests
* Add file manipulation errors handling