# FilterInput
Static wrapper functions for $_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER and $_ENV

Functions, what are included (with beginning corresponding global variable name) are:
* raw
* string
* stringUrlEncoded
* stringMagicQuotes
* html
* float
* integer
* email
* url
* ip

Example:
```php
require_once 'FilterInput.class.php';
use jyri78\FilterInput as fi;

// Will output email address or empty string (if not validated)
echo fi::requestEmail('email');
```
