# PhpClosure

[![Build Status](https://secure.travis-ci.org/loginwashere/PhpClosure.png?branch=master)](https://travis-ci.org/loginwashere/PhpClosure)

This is a fork of [php-closure](http://code.google.com/p/php-closure/) class
for working with google closure compiler api that I found on Google Code

## Documentation
Will be available on [phpclosure.readthedocs.org](https://phpclosure.readthedocs.org/en/latest/)

## Installation

It is assumed that library will be used with composer, so installation steps are:

1. Install [composer](http://getcomposer.org/doc/00-intro.md)
2. Add resent wersion of library to composer.json of your project.

```json
{
    "require": {
        "loginwashere/php-closure": "*"
    }
}
```

3. run command `php path/to/composer.phar install` (or `php path/to/composer.phar update` - if you've already executed install)


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

For compiler options description go to [official documentation](https://developers.google.com/closure/compiler/docs/overview)

### Todo

* Add tests
* Add file manipulation errors handling