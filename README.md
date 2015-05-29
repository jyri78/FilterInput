# [FilterInput](https://github.com/jyri78/FilterInput/)
Static wrapper functions for `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_SERVER` and `$_ENV`.

##Requirements
* PHP 5.3.3+  (on most cases works with PHP 5.2+)

## QuickStart
Functions, what are included (with beginning corresponding global variable name) are:
* Raw
* String
* StringUrlEncoded
* StringMagicQuotes
* Html
* Float
* Integer
* Email
* Url
* Ip

### More about functions
For example, for `$_POST` functions would be as follows (same goes for other globals):

```php
// Returns unsanitized (but trimmed) string from '$_POST'
postRaw($variable, $falseNullToEmptyString = true)
```
**string** `$variable` – key of _$GLOBAL_, like `$_POST` etc.

**bool** `$falseNullToEmptyString` – function will return empty string instead of 'false' or 'null' (default 'true')

```php
// Returns sanitized string from '$_POST'
postString($variable, $encodeHigh = false, $falseNullToEmptyString = true)
```
**bool** `$encodeHigh` – encodes all characters with a numerical value >127 (default 'false')

```php
// Returns URL-encoded string from '$_POST'
postStringUrlEncoded($variable, $falseNullToEmptyString = true)
```

```php
// Applies {@link http://php.net/manual/en/function.addslashes.php addslashes()} to the string
postStringMagicQuotes($variable, $falseNullToEmptyString = true)
```

```php
// Returns HTML-escaped string (like with
// {@link http://php.net/manual/en/function.htmlspecialchars.php htmlspecialchars()}) from '$_POST'
// Requires PHP 5.3.3+
postHtml($variable, $falseNullToEmptyString = true)
```

```php
// Validates and returns value as float from '$_POST'
postFloat($variable, $nullToFalse = true)
```
**bool** `$nullToFalse` – function will return boolean false instead of 'null' (default 'true')

```php
// Validates and returns value as integer from '$_POST'
postInteger($variable, $nullToFalse = true)
```

```php
// Returns email-address (if validates) or boolean false from '$_POST'
postEmail($variable, $falseNullToEmptyString = true)
```

```php
// Returns URL (if validates) or boolean false from '$_POST'
postUrl($variable, $addShemeIfNecessary = true, $falseNullToEmptyString = true)
```
**bool** `$addShemeIfNecessary` – adds 'http://' to the string, if no sheme recognized, for validation (default 'true')

```php
// Returns IP (if validates) or boolean false from '$_POST'
postIp($variable, $noLoopbackRange = false, $noPrivateRange = false, $noReservedRange = false,
        $falseNullToEmptyString = true)
```
**bool** `$noLoopbackRange` – loopback (or localhost) address range is not allowed in validation (default 'false');

**bool** `$noPrivateRange` – private address range is not allowed in validation (default 'false');

**bool** `$noReservedRange` – reserved address range is not allowed in validation (default 'false');

**bool** `$falseNullToEmptyString` – function will return empty string instead of 'false' or 'null'. If IP does not validate, then func will return boolean false regardless of the setting (default 'true').

##Example:
```php
require_once 'FilterInput.class.php';
use jyri78\FilterInput as fi;

// Will output email address or empty string (if not validated)
echo fi::requestEmail('email');
```

#Licence
See [LICENSE](LICENSE)
