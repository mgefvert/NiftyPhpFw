<?php

/**
 * Base class for most Nifty exceptions
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Exception extends Exception
{
    public $httpErrorCode = 500;
    public $httpErrorMessage = 'Application fault';
    public $directOutput = false;
}

/**
 * Assertion failed exception.
 */
class NF_EAssertionFailed extends NF_Exception
{
    public static function assert($test, $msg = null)
    {
        if (!$test)
            throw new NF_EAssertionFailed($msg);
    }

    public static function failIf($test, $msg = null)
    {
        self::assert(!$test, $msg);
    }
}

/**
 * Database error exception.
 */
class NF_EDatabaseError extends NF_Exception
{
}

/**
 * Date error - invalid date/time etc
 */
class NF_EDateError extends NF_Exception
{
}

/**
 *  Invalid request.
 */
class NF_EInvalidRequest extends NF_Exception
{
    public $httpErrorCode = 400;
    public $httpErrorMessage = 'Bad request';
}

/**
 *  Method not allowed
 */
class NF_EMethodNotAllowed extends NF_Exception
{
    public $httpErrorCode = 405;
    public $httpErrorMessage = 'Method not allowed';
}

/**
 * Not modified
 */
class NF_ENotModified extends NF_Exception
{
    public $httpErrorCode = 304;
    public $httpErrorMessage = 'Not modified';
    public $directOutput = true;

    public function displayException()
    {
        return '';
    }
}

/**
 * Authentication failed - meaning that the user's access credentials were
 * invalid or expired. Should lead to a "login again" page.
 */
class NF_EAuthenticationFailed extends NF_Exception
{
    public $httpErrorCode = 403;
    public $httpErrorMessage = 'Authentication Failed';

    public function displayException()
    {
        return <<<EOF
<h1>403 Authentication Failed</h1>

<p>Authentication to this system failed. Check your access credentials and try again.</p>
EOF;
    }
}

/**
 * Authorization failed - meaning that the user has been authenticated, but
 * the access credentials do not have authorization to access a particular
 * resource. Should *not* result in any "try logging in again" page as the
 * user already is logged in successfully.
 */
class NF_EAuthorizationFailed extends NF_Exception
{
    public $httpErrorCode = 403;
    public $httpErrorMessage = 'Authorization failed';

    public function displayException()
    {
        return <<<EOF
<h1>403 Authorization failed</h1>

<p>You are not authorized to access this resource.</p>
EOF;
    }
}

/**
 * Authorization failed - meaning that the user has been authenticated, but
 * the access credentials do not have authorization to access a particular
 * resource. Should *not* result in any "try logging in again" page as the
 * user already is logged in successfully.
 */
class NF_ESecurityViolation extends NF_Exception
{
    public $httpErrorCode = 403;
    public $httpErrorMessage = 'Security Violation';

    public function displayException()
    {
        return <<<EOF
<h1>403 Security Violation</h1>

<p>A security violation occurred.</p>
EOF;
    }
}

/**
 *  Page not found exception.
 */
class NF_EPageNotFound extends NF_Exception
{
    public $httpErrorCode = 404;
    public $httpErrorMessage = 'Resource not found';

    public function displayException()
    {
        $uri = html($_SERVER['REQUEST_URI']);

        return <<<EOF
<h1>404 Resource not found</h1>

<p>The resource <tt>$uri</tt> could not be located.</p>
EOF;
    }
}

/**
 *  Validation failed exception.
 */
class NF_EValidationFailed extends NF_Exception
{
}

/**
 *  Default exception handler
 */
function displayException($e)
{
    $trace = '';

    if (NF::config()->debug->stack_trace)
        $trace = html($e->getTraceAsString());

    $type = get_class($e);

    return <<<EOF
<h1>Oops!</h1>

<p>An exception was unhandled by the framework.</p>

<pre>
Type      <b>$type - {$e->getMessage()}</b>
Code      <b>{$e->getCode()}</b>
In file   <b>{$e->getFile()}</b> at line <b>{$e->getLine()}</b>

{$trace}
</pre>
EOF;
}
