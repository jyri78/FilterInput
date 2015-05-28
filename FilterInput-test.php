<!DOCTYPE html>
<!--
Copyright (c) 2014, Jüri Kormik
All rights reserved.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>FilterInput-s test</title>
    </head>
    <body style="padding:40px">
        <h1>FilterInput-s test</h1>
        <table style="border:0"><tr><td>
        <?php
        require_once 'FilterInput.class.php';
        use jyri78\FilterInput as fi;

        if ( fi::requestRaw('test') ) {
            echo '<pre>'. "\n    GET:\n——————————————————————\n";
            echo "\nString              –  ". fi::getString('gStr');
            echo "\nURL Encoded String  –  ". fi::getStringUrlEncoded('gStrUE');
            echo "\nMagic Quotes        –  ". fi::getStringMagicQuotes('gStrMQ');
            echo "\nHTML Special Chars  –  ". fi::getHtml('gHtml') .'  <i style="color:#999">(look at page source)</i>';
            echo "\nFloat value         –  ". fi::getFloat('gFloat');
            echo "\nInteger value       –  ". fi::getInteger('gInt');
            echo "\nE-mail address      –  ". fi::getEmail('gEmail');
            echo "\nURL address         –  ". fi::getUrl('gUrl');
            echo "\nIP address          –  ". fi::getIp('gIp');
            echo "\nIP (no Private)     –  ". fi::postIp('gIp', FALSE, TRUE);

            echo "\n\n\n    POST:\n——————————————————————\n";
            echo "\nString (enc. high)  –  ". fi::postString('pStr', TRUE) .'  <i style="color:#999">(look at page source)</i>';
            echo "\nURL Encoded String  –  ". fi::postStringUrlEncoded('pStrUE');
            echo "\nMagic Quotes        –  ". fi::postStringMagicQuotes('pStrMQ');
            echo "\nHTML Special Chars  –  ". fi::postHtml('pHtml') .'  <i style="color:#999">(look at page source)</i>';
            echo "\nFloat value         –  ". fi::postFloat('pFloat');
            echo "\nInteger value       –  ". fi::postInteger('pInt');
            echo "\nE-mail address      –  ". fi::postEmail('pEmail');
            echo "\nURL address         –  ". fi::postUrl('pUrl');
            echo "\nIP address          –  ". fi::postIp('pIp');
            echo "\nIP (no Loopback)    –  ". fi::postIp('pIp', TRUE);
            echo "\nIP (no Private)     –  ". fi::postIp('pIp', FALSE, TRUE);
            echo "\nIP (no Reserved)    –  ". fi::postIp('pIp', FALSE, FALSE, TRUE);

            echo "\n\n\n    REQUEST:\n——————————————————————\n";
            echo "\nURL Encoded String  –  ". fi::requestStringUrlEncoded('pStrUE');
            echo "\nMagic Quotes        –  ". fi::requestStringMagicQuotes('pStrMQ');
            echo "\nFloat value (GET)   –  ". fi::requestFloat('gFloat');
            echo "\nE-mail (GET-str.)   –  ". fi::requestEmail('gEmail');

            echo "\n\n\n    SERVER:\n——————————————————————\n";
            echo "\nIP (REMOTE_ADDR)        –  ". fi::serverIp('REMOTE_ADDR');
            echo "\nInteger (REMOTE_PORT)   –  ". fi::serverInteger('REMOTE_PORT');
            echo "\nEmail (SERVER_ADMIN)    –  ". fi::serverEmail('SERVER_ADMIN') .'  <i style="color:#999">(in localhost doesn\'t validate)</i>';
            echo "\nURL addr. (SCRIPT_URI)  –  ". fi::serverUrl('SCRIPT_URI') .'  <i style="color:#999">(in localhost might not be set)</i>';
            echo "\n</pre>";
        }



        // Code taken from http://php.net/manual/en/function.filter-var.php
        function validate_email($email, $strict = true) {
            $dot_string = $strict ?
                '(?:[A-Za-z0-9!#$%&*+=?^_`{|}~\'\\/-]|(?<!\\.|\\A)\\.(?!\\.|@))' :
                '(?:[A-Za-z0-9!#$%&*+=?^_`{|}~\'\\/.-])'
            ;
            $quoted_string = '(?:\\\\\\\\|\\\\"|\\\\?[A-Za-z0-9!#$%&*+=?^_`{|}~()<>[\\]:;@,. \'\\/-])';
            $ipv4_part = '(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])';
            $ipv6_part = '(?:[A-fa-f0-9]{1,4})';
            $fqdn_part = '(?:[A-Za-z](?:[A-Za-z0-9-]{0,61}?[A-Za-z0-9])?)';
            $ipv4 = "(?:(?:{$ipv4_part}\\.){3}{$ipv4_part})";
            $ipv6 = '(?:' .
                "(?:(?:{$ipv6_part}:){7}(?:{$ipv6_part}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){6}(?::{$ipv6_part}|:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){5}(?:(?::{$ipv6_part}){1,2}|:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){4}(?:(?::{$ipv6_part}){1,3}|(?::{$ipv6_part})?:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){3}(?:(?::{$ipv6_part}){1,4}|(?::{$ipv6_part}){0,2}:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){2}(?:(?::{$ipv6_part}){1,5}|(?::{$ipv6_part}){0,3}:{$ipv4}|:))" . '|' .
                "(?:(?:{$ipv6_part}:){1}(?:(?::{$ipv6_part}){1,6}|(?::{$ipv6_part}){0,4}:{$ipv4}|:))" . '|' .
                "(?::(?:(?::{$ipv6_part}){1,7}|(?::{$ipv6_part}){0,5}:{$ipv4}|:))" .
            ')';
            $fqdn = "(?:(?:{$fqdn_part}\\.)+?{$fqdn_part})";
            $local = "({$dot_string}++|(\"){$quoted_string}++\")";
            $domain = "({$fqdn}|\\[{$ipv4}]|\\[{$ipv6}]|\\[{$fqdn}])";
            $pattern = "/\\A{$local}@{$domain}\\z/";
            return preg_match($pattern, $email, $matches) &&
                (
                    !empty($matches[2]) && !isset($matches[1][66]) && !isset($matches[0][256]) ||
                    !isset($matches[1][64]) && !isset($matches[0][254])
                )
            ;
        }

        // Also by function above (and by some more validators like RFC822 Parser
        // http://code.iamcal.com/php/rfc822/ ) this email doesn't validate (but should be... or not?).
//        echo "\n\n" .'<br />"postmaster@localhost" <span style="color:#f63;font-weight:bold">'.
//                ( validate_email('postmaster@localhost') ? 'is valid email' : 'is NOT valid email' ) .
//                '</span>';
        ?>

        </td><td style="padding-left:35px">
        <form action="FilterInput-test.php?gStr=Jüri&amp;gStrUE=Hello%20world!&amp;gStrMQ=rock'n'roll&amp;gHtml='html%20string'&amp;gFloat=1,234.445&amp;gInt=4657bx3d&amp;gEmail=name@example.com&amp;gUrl=www.example.com&amp;gIp=192.168.0.55"
                style="margin-top:35px;border:1px dashed #ccc" method="post">
            <input type="hidden" name="test" value="true" />
            <table style="border:0;padding:10px">
                <tr><td>String</td><td><input type="text" name="pStr" size="35" value="<?php $s=fi::postRaw('pStr'); echo ( $s ? $s : 'Jüri'); ?>" /></td></tr>
                <tr><td>URL Encoded String  </td><td><input type="text" name="pStrUE" size="35" value="<?php $s=fi::postRaw('pStrUE'); echo ( $s ? $s : 'more string'); ?>" /></td></tr>
                <tr><td>Magic Quotes</td><td><input type="text" name="pStrMQ" size="35" value="<?php $s=fi::postRaw('pStrMQ'); echo ( $s ? $s : 'name: O\'Brian'); ?>" /></td></tr>
                <tr><td>HTML Special Chars  </td><td><input type="text" name="pHtml" size="35" value="<?php $s=fi::postRaw('pHtml'); echo ( $s ? $s : 'rock & roll'); ?>" /></td></tr>
                <tr><td>Float value</td><td><input type="text" name="pFloat" size="35" value="<?php $s=fi::postRaw('pFloat'); echo ( $s ? $s : '124,65'); ?>" /></td></tr>
                <tr><td>Integer value</td><td><input type="text" name="pInt" size="35" value="<?php $s=fi::postRaw('pInt'); echo ( $s ? $s : '451gh3'); ?>" /></td></tr>
                <tr><td>E-mail addressg</td><td><input type="text" name="pEmail" size="35" value="<?php $s=fi::postRaw('pEmail'); echo ( $s ? $s : 'example@example.com'); ?>" /></td></tr>
                <tr><td>URL address</td><td><input type="text" name="pUrl" size="35" value="<?php $s=fi::postRaw('pUrl'); echo ( $s ? $s : 'www.google.com'); ?>" /></td></tr>
                <tr><td>IP address</td><td><input type="text" name="pIp" size="35" value="<?php $s=fi::postRaw('pIp'); echo ( $s ? $s : '::1'); ?>" /></td></tr>
                <tr><td colspan="2"> </td></tr>
                <tr><td> </td><td><input type="submit" name="submit" value="POST a values" /></td></tr>
            </table>
        </form>
        </td></tr></table>
    </body>
</html>
