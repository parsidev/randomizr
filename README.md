Randomizr: generating random madness
====================================


Randomizr is a simple package that helps you generate all kinds of random stuff (hashes, strings, numbers, ...). It can even check whether the created random is unique within a given database table or directory.

Table of contents
-----------------

1. [Installation](#1-installation)     
2. [Usage](#2-usage)    
    2.1. [Charsets](#21-charsets)   
    2.2. [Aliases](#22-aliases)  
    2.3. [Generating unique randoms](#23-generating-unique-randoms)     
    2.4. [Using the Facade](#24-using-the-facade)   
    2.5. [Additional helpers](#25-additional-helpers)   
3. [Customizing](#3-customizing)    
    3.1. [Registering custom charsets](#31-registering-custom-charsets)  
    3.2. [Registering custom aliases](#32-registering-custom-aliases)   


1. Installation
---------------

```
$ composer require parsidev/randomizr
```

Register the `Service Provider`:
```php
'providers' => [
    ...
    Parsidev\Support\Providers\RandomizrServiceProvider::class,
],

// @file app/config/app.php
```

Additionally, you may register an alias for it, though it's not required (you can also use the helper or include the namespace):
```php
'aliases' => [
    ...
    'Randomizr' => Parsidev\Support\Facades\Randomizr::class,
],

// @file app/config/app.php
```

To make sure the package will be loaded properly, run:
```
$ composer update
```


2. Usage
--------

### 2.1. Charsets

Randomizr distincs several character sets, which are:

| Name          | Contents                                      |
|:--------------|:----------------------------------------------|
| `num`         | 0123456789                                    |
| `vowel`       | aeijoy                                        |
| `consonant`   | bcdfghklmnpqrstvwxyz                          |
| `special`     | áäâàæãåāaeéëêèęėēuúüûùūiíïïìiîįīoóöôòõœøōç    |
| `space`       | ' '                                           |
| `dash`        | -                                             |
| `underscore`  | _                                             |
| `punctuation` | :,.?!()                                       |

#### Simple usage
To generate a random using either one of the charset, simply call the `make()` method on the `randomizr()` helper and tell it which charset you want to use:
```php
randomizr('vowel')->make();
randomizr('num')->make();
...
```

#### Setting bounderies
The `make()` method accepts 2 (optional) arguments: `$max` and `$min`:
```php
// Set the max length to 16
randomizr('consonant')->make(16);

// Set the minimum length to 2
randomizr('num')->make(16, 2);
```

#### Combining charsets
You can also mix 'n match different charsets. For example, if you want to generate a random `num`, but also allow a `dash` and an `underscore` in it:
```php
randomizr('num_dash_underscore')->make();
// or, if you prefer
randomizr('numDashUnderscore')->make();
```


### 2.2. Aliases

Though camel- or snake casing is a very convenient way to merge various charsets, you may as well register an alias for it. By default, these are the ones provided:

| Name          | Combines                                      |
|:--------------|:----------------------------------------------|
| `lowercase`   | `vowel`, `consonant`                          |
| `uppercase`   | uppercased `lowercase`                        |
| `alpha`       | `uppercase`, `lowercase`                      |
| `string`      | `alpha`, `num`, `dash`, `space`, `underscore`, `punctuation`, '&$@'   |
| `hash`        | `alpha`, `num`, `dash`, `underscore`, '&$@'   |

So in fact, `lowercase` is an alias to `vowel_constant`. And lets be honest, it's far more cleaner looking.

#### Combining aliases and/or charsets
As you may have seen it already in the table above, aliases also allow to combine charsets with aliases, or even aliases with each other:
```php
// Using charsets only
randomizr('vowel_consonant_num')->make();

// Using the `alpha` alias to merge `vowel` and `consonant`
randomizr('alpha_num')->make();
```


### 2.3 Generating unique randoms

Randomizr can generate randoms that are unique either in a directory or in a database table using the `unique()` method instead of `make()`.

#### Unique in a directory
```php
randomizr('alpha_punctuation')->unique('path/to/dir');
```

#### Unique in a database table (for a field)
```php
randomizr('lowercase_dash')->unique('tablename@field');
```

>**Note**: Checking for uniqueness against a database table requires the package to run in a Laravel application. You won't be able to use this feature elsewhere.

#### Setting bounderies
The same way you can set bouderies for `make()`, you can do it for `unique()`:
```php
randomizr('string')->unique('path/to/dir', 16, 6);
```


### 2.4. Using the Facade
I personally prefer using the helper method (which doesn't require you to put the full namespace at the top of your file), but you can just as well use the `facade` or `alias` (if registered in the app config):
```php
randomizr('alpha_num')->make();
// equals
randomizr('alphaNum')->make();
// equals
Randomizr::alpha_num()->make();
// equals
Randomizr::alphaNum()->make();
```


### 2.5. Additional helpers
In addition to the `randomizr()` helper, a few other helper functions are included that can be used throughout the entire application:

#### Strings

##### str_starts_with
`str_starts_with` checks if a given string starts with a specified (group of) character(s).

```php
str_starts_with('a', 'abc'); // returns true
str_starts_with('b', 'abc'); // returns false
```

##### str_ends_with
`str_ends_with` checks if a given string ends with a specified (group of) character(s).

```php
str_ends_with('de', 'abcde'); // returns true
str_ends_with('cd', 'abcde'); // returns false
```


3. Customizing
--------------

Several charsets and aliases are available by default, but you can add your own ones if you like. All you need to do is config the Randomizr config file:
```
$ php artisan randomizr:publish
```

The config file will be published to `app/config/packages/luminol/randomizr/randomizr.php`.

>**Note**: I made a custom artisan command for this to keep publishing assets the same for both L4 and L5.


### 3.1. Registering custom charsets

Registering custom charsets is pretty straight forward. Just add:
```php
'charsets' => array(
    ...
    'mycharsetname' => 'characters_for_this_charset'
),
```

>**Important**: Charset names should always consise of one word only. Do not use snake- or camel casing (it'll break the combine functionality).


### 3.2. Registering custom aliases

Custom aliases are a bit more configurable: it uses piping to separate the different components to combine. These components can be a raw `string`, `charset` or `alias` and can even be altered using a basic string functions.

To illustrate all of this, let's image you need to generate a random ID, which can contains alpha characters (both upper- and lowercase), dashes and '.'.

#### Combining charsets and/or aliases
The example above tells us the ID alias may contain alpha characters (which is an alias) and dashes (which is a charset). So to combine them in the `id` alias:
```php
'aliases' => array(
    ...
    'id' => "alpha|dash"
),
```

Randomizr will automatically resolve these as an alias and a charset.

#### Adding a raw string to an alias
In our example above, we also need a '.' (dot) to be added. It is available in the `punctuation` charset, but all other characters in this one are not allowed in the ID alias.

We could register the single dot as a charset, but we can also pass it as a raw string to the alias. Just wrap it between `single quotes`:
```php
'aliases' => array(
    ...
    'id' => "alpha|dash|'.'"
),
```

If you want to add a raw number to the alias, no need to wrap it in single quotes (though it will work as well):
```php
'aliases' => array(
    ...
    'id' => "consonant|6", // the result will be 'aeijoy6'
),
```


#### Using simple string functions
Aliases also allow basic string functions to be used. For example, you might only want an uppercased version of the `special` charset. In that case, you can seperate the function name and its argument using `method:arg1,arg2` format:
```php
'aliases' => array(
    ...
    'uspecial' => "strtoupper:special"
),
```

The functions `arguments` can be a `charset`, an `alias` or even a raw `string` (wrapped in single quotes):
```php
'aliases' => array(
    ...
    'notnull'   => "str_replace:0,'',num", // results in '123456789'
    'custom'    => "my_custom_function:'arg1',num"
),
```







