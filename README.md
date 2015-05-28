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

Example:
```php
require_once 'FilterInput.class.php';
use jyri78\FilterInput as fi;

// Will output email address or empty string (if not validated)
echo fi::requestEmail('email');
```

#Licence
See [LICENSE](LICENSE)
