<?php

/*
 * Copyright (c) 2014, Jüri Kormik
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace jyri78;

/**
 * Static wrapper functions for $_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER and $_ENV
 *
 * Functions, what are included (with beginning corresponding global variable name) are:
 * * raw
 * * string
 * * stringUrlEncoded
 * * stringMagicQuotes
 * * html
 * * float
 * * integer
 * * email
 * * url
 * * ip
 *
 * Example:
 * <pre><code>
 * require_once 'FilterInput.class.php';
 * use jyri78\FilterInput as fi;
 *
 * // Will output email address or empty string (if not validated)
 * echo fi::requestEmail('email');
 * </code></pre>
 *
 * @author Jüri Kormik <jyri@jyri78.eu>
 * @copyright (c) 2014, Jüri Kormik
 * @version 0.1 (PHP >= 5.3.3)
 */
class FilterInput
{
    /**#@+
     * Private helper function
     * @access private
     * @ignore
     */
    private static function raw($type, $var, $boolToEmptyStr)
    {
        $ret = filter_input($type, $var);

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }

        if ($ret) $ret = trim($ret);
        return $ret;
    }
    private static function string($type, $var, $encHigh, $boolToEmptyStr)
    {
        $flags = FILTER_FLAG_STRIP_LOW;
        if ($encHigh) $flags |= FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_AMP;
        $ret = filter_input($type, $var, FILTER_SANITIZE_STRING, $flags);

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }

        if ($ret) $ret = trim($ret);
        return $ret;
    }
    private static function stringUrlEnc($type, $var, $boolToEmptyStr)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_ENCODED,
                FILTER_FLAG_STRIP_LOW | FILTER_FLAG_ENCODE_HIGH);

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }

        if ($ret) $ret = trim($ret);
        return $ret;
    }
    private static function stringMagicQuotes($type, $var, $boolToEmptyStr)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_MAGIC_QUOTES);

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }

        if ($ret) $ret = trim($ret);
        return $ret;
    }
    private static function html($type, $var, $boolToEmptyStr)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }

        if ($ret) $ret = trim($ret);
        return $ret;
    }
    /*
     * Idea from http://php.net/manual/en/function.floatval.php
     */
    private static function tofloatStr($num)
    {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');

        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
                ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) return $num;

        return preg_replace("/[\,\ \.]/", "", substr($num, 0, $sep)) . '.' .
                preg_replace("/[\,\ \.]/", "", substr($num, $sep+1, strlen($num)));
    }
    private static function float($type, $var, $nullToFalse)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_SCIENTIFIC);

        if ( $nullToFalse && $ret===null ) return false;

        if ($ret) {
            $r = self::tofloatStr(trim($ret));
            if ( !filter_var($r, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND) ) return false;
            return floatval($r);
        }
        return $ret;
    }
    private static function integer($type, $var, $nullToFalse)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_NUMBER_INT);
        if ( $nullToFalse && $ret===null ) return false;
        if ($ret) $ret = trim($ret);

        if ( !filter_var($ret, FILTER_VALIDATE_INT,
                FILTER_FLAG_ALLOW_OCTAL | FILTER_FLAG_ALLOW_HEX) ) {
            return false;
        }

        return intval($ret, 0);
    }
    private static function email($type, $var, $boolToEmptyStr)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_EMAIL);

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }

        if ($ret) $ret = trim($ret);

        // Maybe it needs to do some custom validator, since filter validator
        // doesn't work 100% correctly.
        // Allegedly (http://stackoverflow.com/questions/3613589/php-email-validation )
        // it is PCRE used for validation (look it also in
        // https://fightingforalostcause.net/content/misc/2006/compare-email-regex.php )
        if ( !filter_var($ret, FILTER_VALIDATE_EMAIL) ) return false;
        return $ret;
    }
    private static function url($type, $var, $addDefSheme, $boolToEmptyStr)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_URL);

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }
        if ($ret) $ret = trim($ret);

        if ($addDefSheme && $ret) {
            if ( strpos($ret, ':/') === false && strpos($ret, ':') === false ) {
                $ret = 'http://'. $ret;
            } elseif ( strpos($ret, ':') > 11 ) {
                $ret = 'http://'. $ret;
            }
        }

        if ( !filter_var($ret, FILTER_VALIDATE_URL) ) return false;
        return $ret;
    }
    private static function ip($type, $var, $noLoopbckRange, $noPrivRange, $noResRange, $boolToEmptyStr)
    {
        $ret = filter_input($type, $var, FILTER_SANITIZE_URL);
        $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;

        if ($boolToEmptyStr) {
            if ( $ret===false || $ret===null ) return '';
        }

        if ($noPrivRange) $flags |= FILTER_FLAG_NO_PRIV_RANGE;
        if ($noResRange)  $flags |= FILTER_FLAG_NO_RES_RANGE;
        if ($ret) $ret = trim($ret);
        if ( !filter_var($ret, FILTER_VALIDATE_IP, $flags) ) return false;

        if ( $noLoopbckRange && ( $ret==='0:0:0:0:0:0:0:1' || $ret==='::1' ||
                (ip2long($ret) & 0xff000000)===0x7f000000 )) {
            return false;
        }

        return $ret;
    }
    /**#@-*/



    /* -------------------------------------------------------------------------
     * Wrapper functions for $_GET
     * -------------------------------------------------------------------------
     */

    /**
     * Returns unsanitized (but trimmed) string from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function getRaw($variable, $falseNullToEmptyString=true)
    {
        return self::raw(INPUT_GET, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns sanitized string from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $encodeHigh             encodes all characters with a numerical value >127 (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function getString($variable, $encodeHigh=false, $falseNullToEmptyString=true)
    {
        return self::string(INPUT_GET, $variable, $encodeHigh, $falseNullToEmptyString);
    }

    /**
     * Returns URL-encoded string from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function getStringUrlEncoded($variable, $falseNullToEmptyString=true)
    {
        return self::stringUrlEnc(INPUT_GET, $variable, $falseNullToEmptyString);
    }

    /**
     * Applies {@link http://php.net/manual/en/function.addslashes.php addslashes()} to the string from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function getStringMagicQuotes($variable, $falseNullToEmptyString=true)
    {
        return self::stringMagicQuotes(INPUT_GET, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns HTML-escaped string (like with {@link http://php.net/manual/en/function.htmlspecialchars.php htmlspecialchars()}) from $_GET
     * @static
     * @param  bool  $variable
     * @param  bool  $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function getHtml($variable, $falseNullToEmptyString=true)
    {
        return self::html(INPUT_GET, $variable, $falseNullToEmptyString);
    }

    /**
     * Validates and returns value as float from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function getFloat($variable, $nullToFalse=true)
    {
        return self::float(INPUT_GET, $variable, $nullToFalse);
    }

    /**
     * Validates and returns value as integer from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function getInteger($variable, $nullToFalse=true)
    {
        return self::integer(INPUT_GET, $variable, $nullToFalse);
    }

    /**
     * Returns email-address (if validates) or boolean false from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if email does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function getEmail($variable, $falseNullToEmptyString=true)
    {
        return self::email(INPUT_GET, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns URL (if validates) or boolean false from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $addShemeIfNecessary    adds 'http://' to the string, if no sheme recognized, for validation (default 'true')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if URL does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function getUrl($variable, $addShemeIfNecessary=true, $falseNullToEmptyString=true)
    {
        return self::url(INPUT_GET, $variable, $addShemeIfNecessary, $falseNullToEmptyString);
    }

    /**
     * Returns IP (if validates) or boolean false from $_GET
     * @static
     * @param  string $variable
     * @param  bool   $noLoopbackRange        loopback (or localhost) address range is not allowed in validation (default 'false')
     * @param  bool   $noPrivateRange         private address range is not allowed in validation (default 'false')
     * @param  bool   $noReservedRange        reserved address range is not allowed in validation (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if IP does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function getIp($variable, $noLoopbackRange=false, $noPrivateRange=false, $noReservedRange=false, $falseNullToEmptyString=true)
    {
        return self::ip(INPUT_GET, $variable, $noLoopbackRange, $noPrivateRange, $noReservedRange, $falseNullToEmptyString);
    }



    /* -------------------------------------------------------------------------
     * Wrapper functions for $_POST
     * -------------------------------------------------------------------------
     */

    /**
     * Returns unsanitized (but trimmed) string from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function postRaw($variable, $falseNullToEmptyString=true)
    {
        return self::raw(INPUT_POST, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns sanitized string from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $encodeHigh             encodes all characters with a numerical value >127 (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function postString($variable, $encodeHigh=false, $falseNullToEmptyString=true)
    {
        return self::string(INPUT_POST, $variable, $encodeHigh, $falseNullToEmptyString);
    }

    /**
     * Returns URL-encoded string from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function postStringUrlEncoded($variable, $falseNullToEmptyString=true)
    {
        return self::stringUrlEnc(INPUT_POST, $variable, $falseNullToEmptyString);
    }

    /**
     * Applies {@link http://php.net/manual/en/function.addslashes.php addslashes()} to the string from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function postStringMagicQuotes($variable, $falseNullToEmptyString=true)
    {
        return self::stringMagicQuotes(INPUT_POST, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns HTML-escaped string (like with {@link http://php.net/manual/en/function.htmlspecialchars.php htmlspecialchars()}) from $_POST
     * @static
     * @param  bool  $variable
     * @param  bool  $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function postHtml($variable, $falseNullToEmptyString=true)
    {
        return self::html(INPUT_POST, $variable, $falseNullToEmptyString);
    }

    /**
     * Validates and returns value as float from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function postFloat($variable, $nullToFalse=true)
    {
        return self::float(INPUT_POST, $variable, $nullToFalse);
    }

    /**
     * Validates and returns value as integer from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function postInteger($variable, $nullToFalse=true)
    {
        return self::integer(INPUT_POST, $variable, $nullToFalse);
    }

    /**
     * Returns email-address (if validates) or boolean false from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if email does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function postEmail($variable, $falseNullToEmptyString=true)
    {
        return self::email(INPUT_POST, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns URL (if validates) or boolean false from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $addShemeIfNecessary    adds 'http://' to the string, if no sheme recognized, for validation (default 'true')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if URL does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function postUrl($variable, $addShemeIfNecessary=true, $falseNullToEmptyString=true)
    {
        return self::url(INPUT_POST, $variable, $addShemeIfNecessary, $falseNullToEmptyString);
    }

    /**
     * Returns IP (if validates) or boolean false from $_POST
     * @static
     * @param  string $variable
     * @param  bool   $noLoopbackRange        loopback (or localhost) address range is not allowed in validation (default 'false')
     * @param  bool   $noPrivateRange         private address range is not allowed in validation (default 'false')
     * @param  bool   $noReservedRange        reserved address range is not allowed in validation (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if IP does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function postIp($variable, $noLoopbackRange=false, $noPrivateRange=false, $noReservedRange=false, $falseNullToEmptyString=true)
    {
        return self::ip(INPUT_POST, $variable, $noLoopbackRange, $noPrivateRange, $noReservedRange, $falseNullToEmptyString);
    }



    /* -------------------------------------------------------------------------
     * Wrapper functions for $_REQUEST;
     * since INPUT_REQUEST is not supported yet, so we have to improvise (with order of PG)
     * -------------------------------------------------------------------------
     */

    /**
     * Returns unsanitized (but trimmed) string from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function requestRaw($variable, $falseNullToEmptyString=true)
    {
        $r = self::raw(INPUT_POST, $variable, $falseNullToEmptyString);
        if (!$r) return self::raw(INPUT_GET, $variable, $falseNullToEmptyString);
        return $r;
    }

    /**
     * Returns sanitized string from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $encodeHigh             encodes all characters with a numerical value >127 (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function requestString($variable, $encodeHigh=false, $falseNullToEmptyString=true)
    {
        $r = self::string(INPUT_POST, $variable, $encodeHigh, $falseNullToEmptyString);
        if (!$r) return self::string(INPUT_GET, $variable, $encodeHigh, $falseNullToEmptyString);
        return $r;
    }

    /**
     * Returns URL-encoded string from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function requestStringUrlEncoded($variable, $falseNullToEmptyString=true)
    {
        $r = self::stringUrlEnc(INPUT_POST, $variable, $falseNullToEmptyString);
        if (!$r) return self::stringUrlEnc(INPUT_GET, $variable, $falseNullToEmptyString);
        return $r;
    }

    /**
     * Applies {@link http://php.net/manual/en/function.addslashes.php addslashes()} to the string from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function requestStringMagicQuotes($variable, $falseNullToEmptyString=true)
    {
        $r = self::stringMagicQuotes(INPUT_POST, $variable, $falseNullToEmptyString);
        if (!$r) return self::stringMagicQuotes(INPUT_GET, $variable, $falseNullToEmptyString);
        return $r;
    }

    /**
     * Returns HTML-escaped string (like with {@link http://php.net/manual/en/function.htmlspecialchars.php htmlspecialchars()}) from $_REQUEST
     * @static
     * @param  bool  $variable
     * @param  bool  $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function requestHtml($variable, $falseNullToEmptyString=true)
    {
        $r = self::html(INPUT_POST, $variable, $falseNullToEmptyString);
        if (!$r) return self::html(INPUT_GET, $variable, $falseNullToEmptyString);
        return $r;
    }

    /**
     * Validates and returns value as float from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function requestFloat($variable, $nullToFalse=true)
    {
        $r = self::float(INPUT_POST, $variable, $nullToFalse);
        if ( $r===false || $r===null ) return self::float(INPUT_GET, $variable, $nullToFalse);
        return $r;
    }

    /**
     * Validates and returns value as integer from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function requestInteger($variable, $nullToFalse=true)
    {
        $r = self::integer(INPUT_POST, $variable, $nullToFalse);
        if ( $r===false || $r===null ) return self::integer(INPUT_GET, $variable, $nullToFalse);
        return $r;
    }

    /**
     * Returns email-address (if validates) or boolean false from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if email does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function requestEmail($variable, $falseNullToEmptyString=true)
    {
        $r = self::email(INPUT_POST, $variable, $falseNullToEmptyString);
        if (!$r) return self::email(INPUT_GET, $variable, $falseNullToEmptyString);
        return $r;
    }

    /**
     * Returns URL (if validates) or boolean false from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $addShemeIfNecessary    adds 'http://' to the string, if no sheme recognized, for validation (default 'true')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if URL does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function requestUrl($variable, $addShemeIfNecessary=true, $falseNullToEmptyString=true)
    {
        $r = self::url(INPUT_POST, $variable, $addShemeIfNecessary, $falseNullToEmptyString);
        if (!$r) return self::url(INPUT_GET, $variable, $addShemeIfNecessary, $falseNullToEmptyString);
        return $r;
    }

    /**
     * Returns IP (if validates) or boolean false from $_REQUEST
     * @static
     * @param  string $variable
     * @param  bool   $noLoopbackRange        loopback (or localhost) address range is not allowed in validation (default 'false')
     * @param  bool   $noPrivateRange         private address range is not allowed in validation (default 'false')
     * @param  bool   $noReservedRange        reserved address range is not allowed in validation (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if IP does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function requestIp($variable, $noLoopbackRange=false, $noPrivateRange=false, $noReservedRange=false, $falseNullToEmptyString=true)
    {
        $r = self::ip(INPUT_POST, $variable, $noLoopbackRange, $noPrivateRange, $noReservedRange, $falseNullToEmptyString);
        if (!$r) return self::ip(INPUT_GET, $variable, $noLoopbackRange, $noPrivateRange, $noReservedRange, $falseNullToEmptyString);
        return $r;
    }



    /* -------------------------------------------------------------------------
     * Wrapper functions for $_COOKIE
     * -------------------------------------------------------------------------
     */

    /**
     * Returns unsanitized (but trimmed) string from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function cookieRaw($variable, $falseNullToEmptyString=true)
    {
        return self::raw(INPUT_COOKIE, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns sanitized string from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $encodeHigh             encodes all characters with a numerical value >127 (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function cookieString($variable, $encodeHigh=false, $falseNullToEmptyString=true)
    {
        return self::string(INPUT_COOKIE, $variable, $encodeHigh, $falseNullToEmptyString);
    }

    /**
     * Returns URL-encoded string from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function cookieStringUrlEncoded($variable, $falseNullToEmptyString=true)
    {
        return self::stringUrlEnc(INPUT_COOKIE, $variable, $falseNullToEmptyString);
    }

    /**
     * Applies {@link http://php.net/manual/en/function.addslashes.php addslashes()} to the string from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function cookieStringMagicQuotes($variable, $falseNullToEmptyString=true)
    {
        return self::stringMagicQuotes(INPUT_COOKIE, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns HTML-escaped string (like with {@link http://php.net/manual/en/function.htmlspecialchars.php htmlspecialchars()}) from $_COOKIE
     * @static
     * @param  bool  $variable
     * @param  bool  $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function cookieHtml($variable, $falseNullToEmptyString=true)
    {
        return self::html(INPUT_COOKIE, $variable, $falseNullToEmptyString);
    }

    /**
     * Validates and returns value as float from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function cookieFloat($variable, $nullToFalse=true)
    {
        return self::float(INPUT_COOKIE, $variable, $nullToFalse);
    }

    /**
     * Validates and returns value as integer from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function cookieInteger($variable, $nullToFalse=true)
    {
        return self::integer(INPUT_COOKIE, $variable, $nullToFalse);
    }

    /**
     * Returns email-address (if validates) or boolean false from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if email does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function cookieEmail($variable, $falseNullToEmptyString=true)
    {
        return self::email(INPUT_COOKIE, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns URL (if validates) or boolean false from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $addShemeIfNecessary    adds 'http://' to the string, if no sheme recognized, for validation (default 'true')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if URL does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function cookieUrl($variable, $addShemeIfNecessary=true, $falseNullToEmptyString=true)
    {
        return self::url(INPUT_COOKIE, $variable, $addShemeIfNecessary, $falseNullToEmptyString);
    }

    /**
     * Returns IP (if validates) or boolean false from $_COOKIE
     * @static
     * @param  string $variable
     * @param  bool   $noLoopbackRange        loopback (or localhost) address range is not allowed in validation (default 'false')
     * @param  bool   $noPrivateRange         private address range is not allowed in validation (default 'false')
     * @param  bool   $noReservedRange        reserved address range is not allowed in validation (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if IP does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function cookieIp($variable, $noLoopbackRange=false, $noPrivateRange=false, $noReservedRange=false, $falseNullToEmptyString=true)
    {
        return self::ip(INPUT_COOKIE, $variable, $noLoopbackRange, $noPrivateRange, $noReservedRange, $falseNullToEmptyString);
    }



    /* -------------------------------------------------------------------------
     * Wrapper functions for $_SERVER
     * -------------------------------------------------------------------------
     */

    /**
     * Returns unsanitized (but trimmed) string from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function serverRaw($variable, $falseNullToEmptyString=true)
    {
        return self::raw(INPUT_SERVER, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns sanitized string from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $encodeHigh             encodes all characters with a numerical value >127 (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function serverString($variable, $encodeHigh=false, $falseNullToEmptyString=true)
    {
        return self::string(INPUT_SERVER, $variable, $encodeHigh, $falseNullToEmptyString);
    }

    /**
     * Returns URL-encoded string from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function serverStringUrlEncoded($variable, $falseNullToEmptyString=true)
    {
        return self::stringUrlEnc(INPUT_SERVER, $variable, $falseNullToEmptyString);
    }

    /**
     * Applies {@link http://php.net/manual/en/function.addslashes.php addslashes()} to the string from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function serverStringMagicQuotes($variable, $falseNullToEmptyString=true)
    {
        return self::stringMagicQuotes(INPUT_SERVER, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns HTML-escaped string (like with {@link http://php.net/manual/en/function.htmlspecialchars.php htmlspecialchars()}) from $_SERVER
     * @static
     * @param  bool  $variable
     * @param  bool  $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function serverHtml($variable, $falseNullToEmptyString=true)
    {
        return self::html(INPUT_SERVER, $variable, $falseNullToEmptyString);
    }

    /**
     * Validates and returns value as float from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function serverFloat($variable, $nullToFalse=true)
    {
        return self::float(INPUT_SERVER, $variable, $nullToFalse);
    }

    /**
     * Validates and returns value as integer from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function serverInteger($variable, $nullToFalse=true)
    {
        return self::integer(INPUT_SERVER, $variable, $nullToFalse);
    }

    /**
     * Returns email-address (if validates) or boolean false from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if email does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function serverEmail($variable, $falseNullToEmptyString=true)
    {
        return self::email(INPUT_SERVER, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns URL (if validates) or boolean false from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $addShemeIfNecessary    adds 'http://' to the string, if no sheme recognized, for validation (default 'true')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if URL does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function serverUrl($variable, $addShemeIfNecessary=true, $falseNullToEmptyString=true)
    {
        return self::url(INPUT_SERVER, $variable, $addShemeIfNecessary, $falseNullToEmptyString);
    }

    /**
     * Returns IP (if validates) or boolean false from $_SERVER
     * @static
     * @param  string $variable
     * @param  bool   $noLoopbackRange        loopback (or localhost) address range is not allowed in validation (default 'false')
     * @param  bool   $noPrivateRange         private address range is not allowed in validation (default 'false')
     * @param  bool   $noReservedRange        reserved address range is not allowed in validation (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if IP does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function serverIp($variable, $noLoopbackRange=false, $noPrivateRange=false, $noReservedRange=false, $falseNullToEmptyString=true)
    {
        return self::ip(INPUT_SERVER, $variable, $noLoopbackRange, $noPrivateRange, $noReservedRange, $falseNullToEmptyString);
    }



    /* -------------------------------------------------------------------------
     * Wrapper functions for $_ENV
     * -------------------------------------------------------------------------
     */

    /**
     * Returns unsanitized (but trimmed) string from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function envRaw($variable, $falseNullToEmptyString=true)
    {
        return self::raw(INPUT_ENV, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns sanitized string from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $encodeHigh             encodes all characters with a numerical value >127 (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function envString($variable, $encodeHigh=false, $falseNullToEmptyString=true)
    {
        return self::string(INPUT_ENV, $variable, $encodeHigh, $falseNullToEmptyString);
    }

    /**
     * Returns URL-encoded string from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function envStringUrlEncoded($variable, $falseNullToEmptyString=true)
    {
        return self::stringUrlEnc(INPUT_ENV, $variable, $falseNullToEmptyString);
    }

    /**
     * Applies {@link http://php.net/manual/en/function.addslashes.php addslashes()} to the string from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function envStringMagicQuotes($variable, $falseNullToEmptyString=true)
    {
        return self::stringMagicQuotes(INPUT_ENV, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns HTML-escaped string (like with {@link http://php.net/manual/en/function.htmlspecialchars.php htmlspecialchars()}) from $_ENV
     * @static
     * @param  bool  $variable
     * @param  bool  $falseNullToEmptyString returns empty string instead of 'false' or 'null' (default 'true')
     * @return mixed
     */
    public static function envHtml($variable, $falseNullToEmptyString=true)
    {
        return self::html(INPUT_ENV, $variable, $falseNullToEmptyString);
    }

    /**
     * Validates and returns value as float from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function envFloat($variable, $nullToFalse=true)
    {
        return self::float(INPUT_ENV, $variable, $nullToFalse);
    }

    /**
     * Validates and returns value as integer from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $nullToFalse returns boolean false instead of 'null' (default 'true')
     * @return mixed
     */
    public static function envInteger($variable, $nullToFalse=true)
    {
        return self::integer(INPUT_ENV, $variable, $nullToFalse);
    }

    /**
     * Returns email-address (if validates) or boolean false from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if email does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function envEmail($variable, $falseNullToEmptyString=true)
    {
        return self::email(INPUT_ENV, $variable, $falseNullToEmptyString);
    }

    /**
     * Returns URL (if validates) or boolean false from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $addShemeIfNecessary    adds 'http://' to the string, if no sheme recognized, for validation (default 'true')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if URL does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function envUrl($variable, $addShemeIfNecessary=true, $falseNullToEmptyString=true)
    {
        return self::url(INPUT_ENV, $variable, $addShemeIfNecessary, $falseNullToEmptyString);
    }

    /**
     * Returns IP (if validates) or boolean false from $_ENV
     * @static
     * @param  string $variable
     * @param  bool   $noLoopbackRange        loopback (or localhost) address range is not allowed in validation (default 'false')
     * @param  bool   $noPrivateRange         private address range is not allowed in validation (default 'false')
     * @param  bool   $noReservedRange        reserved address range is not allowed in validation (default 'false')
     * @param  bool   $falseNullToEmptyString returns empty string instead of 'false' or 'null'; if IP does not validate, then func will return boolean false regardless of the setting (default 'true')
     * @return mixed
     */
    public static function envIp($variable, $noLoopbackRange=false, $noPrivateRange=false, $noReservedRange=false, $falseNullToEmptyString=true)
    {
        return self::ip(INPUT_ENV, $variable, $noLoopbackRange, $noPrivateRange, $noReservedRange, $falseNullToEmptyString);
    }
}
