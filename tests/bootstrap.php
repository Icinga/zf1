<?php

require_once __DIR__ . '/../vendor/autoload.php';

$boolval = static function ($value, $default = false) {
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    return $filtered === null ? $default : $filtered;
};

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . __DIR__ . '/../library'
    . PATH_SEPARATOR . __DIR__ // /tests
);

/**
 * Zend_Controller tests
 *
 * TESTS_ZEND_CONTROLLER_DISPATCHER_OB => test disabling output buffering in dispatcher
 */
defined('TESTS_ZEND_CONTROLLER_DISPATCHER_OB') || define(
    'TESTS_ZEND_CONTROLLER_DISPATCHER_OB',
    $boolval(getenv('TESTS_ZEND_CONTROLLER_DISPATCHER_OB'), false)
);

/**
 * Zend_Http_Client tests
 *
 * To enable the dynamic Zend_Http_Client tests, you will need to symbolically
 * link or copy the files in tests/Zend/Http/Client/_files to a directory
 * under your web server(s) document root and set this constant to point to the
 * URL of this directory.
 */
defined('TESTS_ZEND_HTTP_CLIENT_BASEURI') || define(
    'TESTS_ZEND_HTTP_CLIENT_BASEURI',
    getenv('TESTS_ZEND_HTTP_CLIENT_BASEURI') ?: false
);

/**
 * Zend_Http_Client_Proxy tests
 *
 * HTTP proxy to be used for testing the Proxy adapter. Set to a string of
 * the form 'host:port'. Set to null to skip HTTP proxy tests.
 */
defined('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY') || define(
    'TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY',
    getenv('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY') ?: false
);
defined('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_USER') || define(
    'TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_USER',
    getenv('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_USER') ?: ''
);
defined('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_PASS') || define(
    'TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_PASS',
    getenv('TESTS_ZEND_HTTP_CLIENT_HTTP_PROXY_PASS') ?: ''
);

/**
 * Zend_Loader_Autoloader multi-version support tests
 *
 * ENABLED:      whether or not to run the multi-version tests
 * PATH:         path to a directory containing multiple ZF version installs
 * LATEST:       most recent ZF version in the PATH
 *               e.g., "1.9.2"
 * LATEST_MAJOR: most recent ZF major version in the PATH to test against
 *               e.g., "1.9.2"
 * LATEST_MINOR: most recent ZF minor version in the PATH to test against
 *               e.g., "1.8.4PL1"
 * SPECIFIC:     specific ZF version in the PATH to test against
 *               e.g., "1.7.6"
 * As an example, consider the following tree:
 *     ZendFramework/
 *     |-- 1.9.2
 *     |-- ZendFramework-1.9.1-minimal
 *     |-- 1.8.4PL1
 *     |-- 1.8.4
 *     |-- ZendFramework-1.8.3
 *     |-- 1.7.8
 *     |-- 1.7.7
 *     |-- 1.7.6
 * You would then set the value of "LATEST" and "LATEST_MAJOR" to "1.9.2", and
 * could choose between "1.9.2", "1.8.4PL1", and "1.7.8" for "LATEST_MINOR",
 * and any version number for "SPECIFIC". "PATH" would point to the parent
 * "ZendFramework" directory.
 */
defined('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_ENABLED') || define(
    'TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_ENABLED',
    $boolval(getenv('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_ENABLED'), false)
);
defined('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_PATH') || define(
    'TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_PATH',
    getenv('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_PATH') ?: false
);
defined('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST') || define(
    'TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST',
    getenv('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST') ?: false
);
defined('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST_MAJOR') || define(
    'TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST_MAJOR',
    getenv('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST_MAJOR') ?: false
);
defined('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST_MINOR') || define(
    'TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST_MINOR',
    getenv('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_LATEST_MINOR') ?: false
);
defined('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_SPECIFIC') || define(
    'TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_SPECIFIC',
    getenv('TESTS_ZEND_LOADER_AUTOLOADER_MULTIVERSION_SPECIFIC') ?: false
);

/**
 * Zend_Locale tests
 *
 * If the TESTS_ZEND_LOCALE_FORMAT_SETLOCALE property below is a valid,
 * locally recognized locale (try "locale -a"), then all tests in
 * tests/Zend/Locale/ test suites will execute *after*
 *    setlocale(LC_ALL, TESTS_ZEND_LOCALE_FORMAT_SETLOCALE);
 * Primarily, this switches certain PHP functions to emit "localized" output,
 * including the built-in "to string" for integer and float conversions.
 * Thus, a locale of 'fr_FR' yields number-to-string conversions in a
 * localized form with the decimal place separator chosen via:
 *    setlocale(LC_ALL, 'fr_FR@euro');
 */
defined('TESTS_ZEND_LOCALE_FORMAT_SETLOCALE') || define(
    'TESTS_ZEND_LOCALE_FORMAT_SETLOCALE',
    getenv('TESTS_ZEND_LOCALE_FORMAT_SETLOCALE') ?: false
);

/**
 * Zend_Date tests
 *
 * If the BCMATH_ENABLED property below is false, all arithmetic
 * operations will use ordinary PHP math operators and functions.
 * Otherwise, the bcmath functions will be used for unlimited precision.
 *
 * If the EXTENDED_COVERAGE property below is false, most of the I18N
 * unit tests will not be computed... this speeds tests up to 80 minutes
 * when doing reports.
 */
defined('TESTS_ZEND_LOCALE_BCMATH_ENABLED') || define(
    'TESTS_ZEND_LOCALE_BCMATH_ENABLED',
    $boolval(getenv('TESTS_ZEND_LOCALE_BCMATH_ENABLED'), true)
);
defined('TESTS_ZEND_I18N_EXTENDED_COVERAGE') || define(
    'TESTS_ZEND_I18N_EXTENDED_COVERAGE',
    $boolval(getenv('TESTS_ZEND_I18N_EXTENDED_COVERAGE'), true)
);

/**
 * Zend_Mail_Storage tests
 *
 * TESTS_ZEND_MAIL_SERVER_TESTDIR and TESTS_ZEND_MAIL_SERVER_FORMAT are used for POP3 and IMAP tests.
 * TESTS_ZEND_MAIL_SERVER_FORMAT is the format your test mail server uses: 'mbox' or 'maildir'. The mail
 * storage for the user specified in your POP3 or IMAP tests should be TESTS_ZEND_MAIL_SERVER_TESTDIR. Be
 * careful: it's cleared before copying the files. If you want to copy the files manually set the dir
 * to null (or anything == null).
 *
 * TESTS_ZEND_MAIL_TEMPDIR is used for testing write operations in local storages. If not set (== null)
 * tempnam() is used.
 */
defined('TESTS_ZEND_MAIL_SERVER_TESTDIR') || define(
    'TESTS_ZEND_MAIL_SERVER_TESTDIR',
    getenv('TESTS_ZEND_MAIL_SERVER_TESTDIR') ?: null
);
defined('TESTS_ZEND_MAIL_SERVER_FORMAT') || define(
    'TESTS_ZEND_MAIL_SERVER_FORMAT',
    getenv('TESTS_ZEND_MAIL_SERVER_FORMAT') ?: 'mbox'
);
defined('TESTS_ZEND_MAIL_TEMPDIR') || define(
    'TESTS_ZEND_MAIL_TEMPDIR',
    getenv('TESTS_ZEND_MAIL_TEMPDIR') ?: null
);

/**
 * Zend_Mail_Storage_Pop3 / Zend_Mail_Transport_Pop3
 *
 * IMPORTANT: you need to copy tests/Zend/Mail/_files/test.mbox to your mail
 * if you haven't set TESTS_ZEND_MAIL_SERVER_TESTDIR
 */
defined('TESTS_ZEND_MAIL_POP3_ENABLED') || define(
    'TESTS_ZEND_MAIL_POP3_ENABLED',
    $boolval(getenv('TESTS_ZEND_MAIL_POP3_ENABLED'), false)
);
defined('TESTS_ZEND_MAIL_POP3_HOST') || define(
    'TESTS_ZEND_MAIL_POP3_HOST',
    getenv('TESTS_ZEND_MAIL_POP3_HOST') ?: 'localhost'
);
defined('TESTS_ZEND_MAIL_POP3_USER') || define(
    'TESTS_ZEND_MAIL_POP3_USER',
    getenv('TESTS_ZEND_MAIL_POP3_USER') ?: 'test'
);
defined('TESTS_ZEND_MAIL_POP3_PASSWORD') || define(
    'TESTS_ZEND_MAIL_POP3_PASSWORD',
    getenv('TESTS_ZEND_MAIL_POP3_PASSWORD') ?: ''
);
// test SSL connections if enabled in your test server
defined('TESTS_ZEND_MAIL_POP3_SSL') || define(
    'TESTS_ZEND_MAIL_POP3_SSL',
    $boolval(getenv('TESTS_ZEND_MAIL_POP3_SSL'), true)
);
defined('TESTS_ZEND_MAIL_POP3_TLS') || define(
    'TESTS_ZEND_MAIL_POP3_TLS',
    $boolval(getenv('TESTS_ZEND_MAIL_POP3_TLS'), true)
);
// WRONG_PORT should be an existing server port,
// INVALID_PORT should be a non existing (each on defined host)
defined('TESTS_ZEND_MAIL_POP3_WRONG_PORT') || define(
    'TESTS_ZEND_MAIL_POP3_WRONG_PORT',
    getenv('TESTS_ZEND_MAIL_POP3_WRONG_PORT') ?: 80
);
defined('TESTS_ZEND_MAIL_POP3_INVALID_PORT') || define(
    'TESTS_ZEND_MAIL_POP3_INVALID_PORT',
    getenv('TESTS_ZEND_MAIL_POP3_INVALID_PORT') ?: 3141
);

/**
 * Zend_Mail_Storage_Imap / Zend_Mail_Transport_Imap
 *
 * IMPORTANT: you need to copy tests/Zend/Mail/_files/test.mbox to your mail
 * if you haven't set TESTS_ZEND_MAIL_SERVER_TESTDIR
 */
defined('TESTS_ZEND_MAIL_IMAP_ENABLED') || define(
    'TESTS_ZEND_MAIL_IMAP_ENABLED',
    $boolval(getenv('TESTS_ZEND_MAIL_IMAP_ENABLED'), false)
);
defined('TESTS_ZEND_MAIL_IMAP_HOST') || define(
    'TESTS_ZEND_MAIL_IMAP_HOST',
    getenv('TESTS_ZEND_MAIL_IMAP_HOST') ?: 'localhost'
);
defined('TESTS_ZEND_MAIL_IMAP_USER') || define(
    'TESTS_ZEND_MAIL_IMAP_USER',
    getenv('TESTS_ZEND_MAIL_IMAP_USER') ?: 'test'
);
defined('TESTS_ZEND_MAIL_IMAP_PASSWORD') || define(
    'TESTS_ZEND_MAIL_IMAP_PASSWORD',
    getenv('TESTS_ZEND_MAIL_IMAP_PASSWORD') ?: ''
);
// test SSL connections if enabled in your test server
defined('TESTS_ZEND_MAIL_IMAP_SSL') || define(
    'TESTS_ZEND_MAIL_IMAP_SSL',
    $boolval(getenv('TESTS_ZEND_MAIL_IMAP_SSL'), true)
);
defined('TESTS_ZEND_MAIL_IMAP_TLS') || define(
    'TESTS_ZEND_MAIL_IMAP_TLS',
    $boolval(getenv('TESTS_ZEND_MAIL_IMAP_TLS'), true)
);
// WRONG_PORT should be an existing server port,
// INVALID_PORT should be a non-existing (each on defined host)
defined('TESTS_ZEND_MAIL_IMAP_WRONG_PORT') || define(
    'TESTS_ZEND_MAIL_IMAP_WRONG_PORT',
    getenv('TESTS_ZEND_MAIL_IMAP_WRONG_PORT') ?: 80
);
defined('TESTS_ZEND_MAIL_IMAP_INVALID_PORT') || define(
    'TESTS_ZEND_MAIL_IMAP_INVALID_PORT',
    getenv('TESTS_ZEND_MAIL_IMAP_INVALID_PORT') ?: 3141
);

/**
 * Zend_Mail_Storage_Maildir test
 *
 * Before enabling this test you have to unpack messages.tar in
 * Zend/Mail/_files/test.maildir/cur/ and remove the tar for this test to work.
 * That's because the messages files have a colon in the filename and that's a
 * forbidden character on Windows.
 */
defined('TESTS_ZEND_MAIL_MAILDIR_ENABLED') || define(
    'TESTS_ZEND_MAIL_MAILDIR_ENABLED',
    $boolval(getenv('TESTS_ZEND_MAIL_MAILDIR_ENABLED'), false)
);

/**
 * Zend_Mail_Transport_Smtp tests
 */
defined('TESTS_ZEND_MAIL_SMTP_ENABLED') || define(
    'TESTS_ZEND_MAIL_SMTP_ENABLED',
    $boolval(getenv('TESTS_ZEND_MAIL_SMTP_ENABLED'), false)
);
defined('TESTS_ZEND_MAIL_SMTP_HOST') || define(
    'TESTS_ZEND_MAIL_SMTP_HOST',
    getenv('TESTS_ZEND_MAIL_SMTP_HOST') ?: 'localhost'
);
defined('TESTS_ZEND_MAIL_SMTP_PORT') || define(
    'TESTS_ZEND_MAIL_SMTP_PORT',
    getenv('TESTS_ZEND_MAIL_SMTP_PORT') ?: 25
);
defined('TESTS_ZEND_MAIL_SMTP_USER') || define(
    'TESTS_ZEND_MAIL_SMTP_USER',
    getenv('TESTS_ZEND_MAIL_SMTP_USER') ?: 'testuser'
);
defined('TESTS_ZEND_MAIL_SMTP_PASSWORD') || define(
    'TESTS_ZEND_MAIL_SMTP_PASSWORD',
    getenv('TESTS_ZEND_MAIL_SMTP_PASSWORD') ?: 'testpassword'
);
defined('TESTS_ZEND_MAIL_SMTP_AUTH') || define(
    'TESTS_ZEND_MAIL_SMTP_AUTH',
    $boolval(getenv('TESTS_ZEND_MAIL_SMTP_AUTH'), false)
);

/**
 * Zend_Uri tests
 *
 * Setting CRASH_TEST_ENABLED to true will enable some tests that may
 * potentially crash PHP on some systems, due to very deep-nesting regular
 * expressions.
 *
 * Only do this if you know what you are doing!
 */
defined('TESTS_ZEND_URI_CRASH_TEST_ENABLED') || define(
    'TESTS_ZEND_URI_CRASH_TEST_ENABLED',
    $boolval(getenv('TESTS_ZEND_URI_CRASH_TEST_ENABLED'), false)
);

/**
 * Zend_Version tests
 *
 * Set ONLINE_ENABLED if you wish to run validators that require network
 * connectivity.
 */
defined('TESTS_ZEND_VERSION_ONLINE_ENABLED') || define(
    'TESTS_ZEND_VERSION_ONLINE_ENABLED',
    $boolval(getenv('TESTS_ZEND_VERSION_ONLINE_ENABLED'), false)
);

/**
 * Zend_Validate tests
 *
 * Set ONLINE_ENABLED if you wish to run validators that require network
 * connectivity.
 */
defined('TESTS_ZEND_VALIDATE_ONLINE_ENABLED') || define(
    'TESTS_ZEND_VALIDATE_ONLINE_ENABLED',
    $boolval(getenv('TESTS_ZEND_VALIDATE_ONLINE_ENABLED'), false)
);

/**
 * Resources translations ('all' for all translations or 'fr', 'de', ...)
 */
defined('TESTS_ZEND_RESOURCES_TRANSLATIONS') || define(
    'TESTS_ZEND_RESOURCES_TRANSLATIONS',
    getenv('TESTS_ZEND_RESOURCES_TRANSLATIONS') ?: 'all'
);

unset($boolval);
