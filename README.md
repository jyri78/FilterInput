# FilterInput
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
For example, for `$_POST` functions would be as follows:

```php
postRaw($variable, $falseNullToEmptyString = true)
```
Returns unsanitized (but trimmed) string from `$_POST`;
**bool** `$falseNullToEmptyString` – function will return empty string instead of 'false' or 'null' (default 'true')

```php
postString($variable, $encodeHigh = false, $falseNullToEmptyString = true)
```
Returns sanitized string from `$_POST`;
**bool** `$encodeHigh` – encodes all characters with a numerical value >127 (default 'false')

```php
postStringUrlEncoded($variable, $falseNullToEmptyString = true)
```
Returns URL-encoded string from `$_POST`

```php
postStringMagicQuotes($variable, $falseNullToEmptyString = true)
```
Applies [addslashes()](http://php.net/manual/en/function.addslashes.php) to the string from `$_POST`

```php
postHtml($variable, $falseNullToEmptyString = true)
```
Returns HTML-escaped string (like with [htmlspecialchars()](http://php.net/manual/en/function.htmlspecialchars.php)) from `$_POST`

```php
postFloat($variable, $nullToFalse = true)
```
Validates and returns value as float from `$_POST`;
**bool** `$nullToFalse` – function will return boolean false instead of 'null' (default 'true')

```php
postInteger($variable, $nullToFalse = true)
```
Validates and returns value as integer from `$_POST`

```php
postEmail($variable, $falseNullToEmptyString = true)
```
Returns email-address (if validates) or boolean false from `$_POST`

```php
postUrl($variable, $addShemeIfNecessary = true, $falseNullToEmptyString = true)
```
Returns URL (if validates) or boolean false from `$_POST`;
**bool** `$addShemeIfNecessary` – adds 'http://' to the string, if no sheme recognized, for validation (default 'true')

```php
postIp($variable, $noLoopbackRange = false, $noPrivateRange = false, $noReservedRange = false, $falseNullToEmptyString = true)
```
Returns IP (if validates) or boolean false from `$_POST`.
**bool** `$noLoopbackRange` – loopback (or localhost) address range is not allowed in validation (default 'false');
**bool** `$noPrivateRange` – private address range is not allowed in validation (default 'false');
**bool** `$noReservedRange` – reserved address range is not allowed in validation (default 'false');
**bool** `$falseNullToEmptyString` – function will return empty string instead of 'false' or 'null'. If IP does not validate, then func will return boolean false regardless of the setting (default 'true')

##Example:
```php
require_once 'FilterInput.class.php';
use jyri78\FilterInput as fi;

// Will output email address or empty string (if not validated)
echo fi::requestEmail('email');
```

#Licence
See [LICENSE](LICENSE)
